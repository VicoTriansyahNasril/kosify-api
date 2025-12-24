<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'tenant_name' => $this->tenant->name ?? 'Unknown',
            'tenant_phone' => $this->tenant->phone ?? '',
            'room_name' => $this->room->name ?? '-',
            'type' => $this->type,
            'type_label' => ucfirst($this->type->value),
            'amount' => (float) $this->amount,
            'amount_formatted' => 'Rp ' . number_format($this->amount, 0, ',', '.'),
            'status' => $this->status,
            'due_date' => $this->due_date->format('d M Y'),
            'created_at' => $this->created_at->format('d M Y'),
        ];
    }
}