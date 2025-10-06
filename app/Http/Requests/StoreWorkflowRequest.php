<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'nodes' => 'required|array|min:1',
            'nodes.*.id' => 'required|string',
            'nodes.*.type' => 'required|string',
            'nodes.*.name' => 'nullable|string',
            'nodes.*.parameters' => 'nullable|array',
            'nodes.*.position' => 'nullable|array',
            'nodes.*.credentials' => 'nullable|integer|exists:credentials,id',
            'connections' => 'required|array',
            'connections.*.source' => 'required|string',
            'connections.*.target' => 'required|string',
            'connections.*.sourceOutput' => 'nullable|string',
            'connections.*.targetInput' => 'nullable|string',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}