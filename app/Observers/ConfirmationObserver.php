<?php

namespace App\Observers;

use App\Models\Confirmation;
use Illuminate\Support\Facades\Http;

class ConfirmationObserver
{
    /**
     * Handle the Confirmation "creating" event.
     */
    public function creating(Confirmation $confirmation): void
    {
        $confirmation->text = 'Нет данных';
        if ($confirmation->type == 'skhalyk') {
            $data = $this->parseSkHalyk($confirmation->iin);
            if (isset($data['success'])) {
                if($data['success']) {
                    $confirmation->is_confirmed = true;
                    $confirmation->text = 'Подтверждено от 1414';
                } else {
                    $confirmation->text = 'Отклонен смс от 1414';
                }
            } else if (isset($data['message'])) {
                $confirmation->text = $data['message'];
            } else if(isset($data['status'])) {
                if($data['status'] == 'PENDING') {
                    $confirmation->text = 'Отправлено смс от 1414';
                }
            }
        } elseif ($confirmation->type == 'freedom') {
            $data = $this->parseFreedom($confirmation->iin);
            if (isset($data['data']) ) {
                if (isset($data['data']['id'])) {
                    if($data['data']['id'] == 1) {
                        $confirmation->is_confirmed = true;
                        $confirmation->text = 'Подтверждено от 1414';
                    } else if($data['data']['id'] == 2) {
                        $confirmation->text = 'Отправлено смс от 1414';
                    } else {
                        $confirmation->text = isset($data['data']['status']) ? $data['data']['status'] : 'Нет данных';
                    }
                }
            }
        }
    }

    private function parseSkHalyk(string $iin): array
    {
        $response = Http::asForm()->post(
            'https://main-backend-site.skhalyk.kz/api/access-status',
            [
                'iin' => $iin,
            ]
        );
        if($response->status() == 200) {
            $data = $response->json();
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    private function parseFreedom(string $iin): array
    {
        $response = Http::asForm()->post(
            'https://ffins-core.ffins.kz/api/v1/portal/access-control',
            [
                'iin' => $iin,
            ]
        );
        if($response->status() == 200) {
            $data = $response->json();
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }
}
