<?php

namespace App\Services\Node;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class NodeRegistry
{
    protected array $nodes = [];
    protected bool $loaded = false;
    
    public function register(string $type, string $class): void
    {
        $this->nodes[$type] = $class;
    }
    
    public function getNode(string $type)
    {
        $this->ensureLoaded();
        
        if (!isset($this->nodes[$type])) {
            throw new \Exception("Node type '{$type}' not found");
        }
        
        return app($this->nodes[$type]);
    }
    
    public function getAllNodes(): array
    {
        $this->ensureLoaded();
        
        return Cache::remember('node_definitions', 3600, function () {
            $definitions = [];
            
            foreach ($this->nodes as $type => $class) {
                $node = app($class);
                $definitions[] = $node->getDefinition();
            }
            
            return $definitions;
        });
    }
    
    public function getNodesByGroup(): array
    {
        $nodes = $this->getAllNodes();
        
        return collect($nodes)->groupBy('group')->toArray();
    }
    
    protected function ensureLoaded(): void
    {
        if ($this->loaded) {
            return;
        }
        
        $this->loadNodes();
        $this->loaded = true;
    }
    
    protected function loadNodes(): void
    {
        // Auto-discover nodes
        $nodePath = app_path('Nodes');
        
        $nodeTypes = [
            'Triggers' => 'trigger',
            'Actions' => 'action',
            'Logic' => 'logic',
            'Transform' => 'transform',
        ];
        
        foreach ($nodeTypes as $folder => $group) {
            $path = $nodePath . '/' . $folder;
            
            if (!File::exists($path)) {
                continue;
            }
            
            $files = File::files($path);
            
            foreach ($files as $file) {
                $className = 'App\\Nodes\\' . $folder . '\\' . $file->getFilenameWithoutExtension();
                
                if (class_exists($className)) {
                    $instance = new $className();
                    $definition = $instance->getDefinition();
                    $this->register($definition['type'], $className);
                }
            }
        }
    }
    
    public function clearCache(): void
    {
        Cache::forget('node_definitions');
    }
}