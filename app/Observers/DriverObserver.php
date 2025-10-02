<?php

namespace App\Observers;

use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DriverObserver
{
    public function creating(Driver $driver)
    {
        if (!$driver->name) {
            $driver->name = 'Нет данных';
        }
    }

    public function saving(Driver $driver): void
    {
        $data = $this->parseNomad($driver->iin);
        if (isset($data['driveClass'])) {
            $driver->class = $data['driveClass'];
            $driver->name = $data['surname'].'. '.$data['name'];
        } else {
            $data = $this->parseNsk($driver->iin);
            if (isset($data['hasData']) && $data['hasData']) {
                $driver->class = $data['result'];
                $driver->name = $data['fullName'];
            }
        }

        if ($driver->iin && !$driver->birth_date) {
            $birthDate = $this->parseIINBirthDate($driver->iin);
            if ($birthDate) {
                $driver->birth_date = $birthDate;
            }
        }
        $driver->age = $driver->birth_date
            ? (int) Carbon::parse($driver->birth_date)->diffInYears(now(), true)
            : null;
    }


    /**
     * Разбирает ИИН и возвращает дату рождения в формате YYYY-MM-DD.
     *
     * @param string $iin  12-значный ИИН
     * @return null|string
     */
    private function parseIINBirthDate(string $iin): ?string
    {
        $iin = trim($iin);

        if (!preg_match('/^\d{12}$/', $iin)) {
            return null;
        }

        // первые 6 цифр — YYMMDD
        $yy = (int)substr($iin, 0, 2);
        $mm = (int)substr($iin, 2, 2);
        $dd = (int)substr($iin, 4, 2);

        // 7-й разряд — код века/пола
        $centuryCode = (int)$iin[6];

        $year = null;

        switch ($centuryCode) {
            case 1: // мужчины, XIX
            case 2: // женщины, XIX
                $year = 1800 + $yy;
                break;

            case 3: // мужчины, XX
            case 4: // женщины, XX
                $year = 1900 + $yy;
                break;

            case 5: // мужчины, XXI
            case 6: // женщины, XXI
                $year = 2000 + $yy;
                break;

            case 0: // иностранные граждане — век не определён однозначно
                // Эвристика: если двухзначный год <= текущего двухзначного, считаем 2000+, иначе 1900+
                $currentTwoDigits = (int)date('y');
                if ($yy <= $currentTwoDigits) {
                    $year = 2000 + $yy;
                } else {
                    $year = 1900 + $yy;
                }
                break;

            default:
                return null;
        }

        // проверяем корректность даты
        if (!checkdate($mm, $dd, $year)) {
            return null;
        }

        $dateString = sprintf('%04d-%02d-%02d', $year, $mm, $dd);

        return $dateString;
    }

    private function parseNomad(string $iin): array
    {
        $response = Http::asForm()->post(
            'https://nomad.kz/ajax/calc/?action=client_get_wt',
            [
                'iin' => $iin,
            ]
        );

        // Если ответ JSON
        $data = $response->json();
        if (is_array($data) && isset($data['success']) && $data['success']) {
            return $data['data'] ?? [];
        }
        return [];
    }

    private function parseNsk(string $iin, string $csrf = 'g0AzCtB7AyXmTg5mK2ArS3UhlMN3whq-Y6iwsDprF77sFlw5nzhnVpcHVy1zOmITDHHdkRuzXugp0uXGdhFe0g==', int $attempt = 1): array
    {
        $response = Http::asForm()->post(
            'https://www.nsk.kz/calculators/ogpo/bonus/?iin='.$iin,
            [
                '_csrf' => $csrf,
            ]
        );
        if($response->status() == 200) {
            $data = $response->json();
            if (is_array($data) && isset($data['hasData'])) {
                return $data;
            }
        } else {
            $newCsrf = $this->getNskCsrf();
            $attempt++;
            if ($attempt < 3) {
                return $this->parseNsk($iin, $newCsrf, $attempt);
            }
        }
        return [];
    }

    private function getNskCsrf(): string
    {
        $response = Http::get('https://www.nsk.kz');
        preg_match('/name="_csrf" value="([^"]+)"/', $response->body(), $matches);
        return $matches[1] ?? '';
    }
}
