<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('workflow'));
    }
    
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'nodes' => 'array|min:1',
            'nodes.*.id' => 'required_with:nodes|string',
            'nodes.*.type' => 'required_with:nodes|string',
            'nodes.*.name' => 'nullable|string',
            'nodes.*.parameters' => 'nullable|array',
            'nodes.*.position' => 'nullable|array',
            'nodes.*.credentials' => 'nullable|integer|exists:credentials,id',
            'connections' => 'array',
            'connections.*.source' => 'required_with:connections|string',
            'connections.*.target' => 'required_with:connections|string',
            'connections.*.sourceOutput' => 'nullable|string',
            'connections.*.targetInput' => 'nullable|string',
            'settings' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}