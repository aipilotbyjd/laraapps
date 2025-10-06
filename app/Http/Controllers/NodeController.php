<?php

namespace App\Http\Controllers;

use App\Services\Node\NodeRegistry;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    protected NodeRegistry $registry;
    
    public function __construct(NodeRegistry $registry)
    {
        $this->registry = $registry;
    }
    
    public function index()
    {
        return response()->json(
            $this->registry->getAllNodes()
        );
    }
    
    public function byGroup()
    {
        return response()->json(
            $this->registry->getNodesByGroup()
        );
    }
    
    public function show(string $type)
    {
        try {
            $node = $this->registry->getNode($type);
            return response()->json($node->getDefinition());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}