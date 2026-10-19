<?php

namespace App\Http\Requests;

class UpdateRecurringTaskRequest extends StoreRecurringTaskRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->recurring_task);
    }
}
