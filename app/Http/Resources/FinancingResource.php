<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'financing_id' => $this->financing_id,
            'financing_amount' => $this->financing_amount,
            'yearly_margin' => $this->yearly_margin,
            'tenor' => $this->tenor,
            'main_payment_periode' => $this->main_payment_periode,
            'margin_payment_periode' => $this->margin_payment_periode,
            'financing_start_date' => $this->financing_start_date
        ];
    }
}
