<?php

namespace App\Nodes\Transform;

use App\Nodes\Base\BaseNode;

class DateTime extends BaseNode
{
    protected string $name = 'Date & Time';
    protected string $type = 'datetime';
    protected string $group = 'transform';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $operation = $parameters['operation'] ?? 'format';
        $date = $this->resolveParameter($parameters['date'] ?? 'now', $input);
        
        return match($operation) {
            'format' => $this->formatDate($date, $parameters['format'] ?? 'Y-m-d H:i:s'),
            'add' => $this->addTime($date, $parameters['amount'] ?? 1, $parameters['unit'] ?? 'days'),
            'subtract' => $this->subtractTime($date, $parameters['amount'] ?? 1, $parameters['unit'] ?? 'days'),
            'diff' => $this->timeDiff($date, $parameters['compareTo'] ?? now()),
            'timezone' => $this->convertTimezone($date, $parameters['fromTimezone'] ?? 'UTC', $parameters['toTimezone'] ?? 'UTC'),
            default => ['formatted' => $date]
        };
    }
    
    protected function formatDate(string $date, string $format): array
    {
        $dateTime = new \DateTime($date);
        return ['formatted' => $dateTime->format($format)];
    }
    
    protected function addTime(string $date, int $amount, string $unit): array
    {
        $dateTime = new \DateTime($date);
        $dateTime->modify("+{$amount} {$unit}");
        return ['result' => $dateTime->format('c')];
    }
    
    protected function subtractTime(string $date, int $amount, string $unit): array
    {
        $dateTime = new \DateTime($date);
        $dateTime->modify("-{$amount} {$unit}");
        return ['result' => $dateTime->format('c')];
    }
    
    protected function timeDiff(string $date1, string $date2): array
    {
        $dt1 = new \DateTime($date1);
        $dt2 = new \DateTime($date2);
        $interval = $dt1->diff($dt2);
        
        return [
            'years' => $interval->y,
            'months' => $interval->m,
            'days' => $interval->d,
            'hours' => $interval->h,
            'minutes' => $interval->i,
            'seconds' => $interval->s,
            'total_days' => $dt1->diff($dt2)->days,
        ];
    }
    
    protected function convertTimezone(string $date, string $fromTz, string $toTz): array
    {
        $dateTime = new \DateTime($date, new \DateTimeZone($fromTz));
        $dateTime->setTimezone(new \DateTimeZone($toTz));
        return ['converted' => $dateTime->format('c')];
    }
    
    protected function getDescription(): string
    {
        return 'Transform date and time values';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'operation',
                'displayName' => 'Operation',
                'type' => 'select',
                'default' => 'format',
                'options' => [
                    ['name' => 'Format', 'value' => 'format'],
                    ['name' => 'Add Time', 'value' => 'add'],
                    ['name' => 'Subtract Time', 'value' => 'subtract'],
                    ['name' => 'Time Difference', 'value' => 'diff'],
                    ['name' => 'Convert Timezone', 'value' => 'timezone'],
                ],
            ],
            [
                'name' => 'date',
                'displayName' => 'Date',
                'type' => 'string',
                'default' => 'now',
                'required' => true,
                'placeholder' => 'Date string or expression like {{ $json.date }}',
            ],
            [
                'name' => 'format',
                'displayName' => 'Format',
                'type' => 'string',
                'default' => 'Y-m-d H:i:s',
                'displayOptions' => [
                    'show' => ['operation' => ['format']]
                ],
            ],
            [
                'name' => 'amount',
                'displayName' => 'Amount',
                'type' => 'number',
                'default' => 1,
                'displayOptions' => [
                    'show' => ['operation' => ['add', 'subtract']]
                ],
            ],
            [
                'name' => 'unit',
                'displayName' => 'Unit',
                'type' => 'select',
                'default' => 'days',
                'options' => [
                    ['name' => 'Seconds', 'value' => 'seconds'],
                    ['name' => 'Minutes', 'value' => 'minutes'],
                    ['name' => 'Hours', 'value' => 'hours'],
                    ['name' => 'Days', 'value' => 'days'],
                    ['name' => 'Weeks', 'value' => 'weeks'],
                    ['name' => 'Months', 'value' => 'months'],
                    ['name' => 'Years', 'value' => 'years'],
                ],
                'displayOptions' => [
                    'show' => ['operation' => ['add', 'subtract']]
                ],
            ],
            [
                'name' => 'compareTo',
                'displayName' => 'Compare To',
                'type' => 'string',
                'displayOptions' => [
                    'show' => ['operation' => ['diff']]
                ],
            ],
            [
                'name' => 'fromTimezone',
                'displayName' => 'From Timezone',
                'type' => 'string',
                'default' => 'UTC',
                'displayOptions' => [
                    'show' => ['operation' => ['timezone']]
                ],
            ],
            [
                'name' => 'toTimezone',
                'displayName' => 'To Timezone',
                'type' => 'string',
                'default' => 'UTC',
                'displayOptions' => [
                    'show' => ['operation' => ['timezone']]
                ],
            ],
        ];
    }
}