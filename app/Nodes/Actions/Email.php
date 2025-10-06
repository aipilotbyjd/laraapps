<?php

namespace App\Nodes\Actions;

use App\Nodes\Base\BaseNode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class Email extends BaseNode
{
    protected string $name = 'Email';
    protected string $type = 'email';
    protected string $group = 'action';
    
    public function execute(array $input, array $parameters, array $credentials = []): array
    {
        $to = $this->resolveParameter($parameters['to'], $input);
        $subject = $this->resolveParameter($parameters['subject'], $input);
        $body = $this->resolveParameter($parameters['body'], $input);
        
        try {
            Mail::send([], [], function (Message $message) use ($to, $subject, $body, $parameters) {
                $message->to($to)
                       ->subject($subject)
                       ->setBody($body, 'text/html');
                
                if (isset($parameters['cc'])) {
                    $cc = $this->resolveParameter($parameters['cc'], $input);
                    $message->cc($cc);
                }
                
                if (isset($parameters['bcc'])) {
                    $bcc = $this->resolveParameter($parameters['bcc'], $input);
                    $message->bcc($bcc);
                }
            });
            
            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'to' => $to,
                'subject' => $subject,
            ];
            
        } catch (\Exception $e) {
            throw new \Exception("Email sending failed: " . $e->getMessage());
        }
    }
    
    protected function getDescription(): string
    {
        return 'Send an email';
    }
    
    protected function getProperties(): array
    {
        return [
            [
                'name' => 'to',
                'displayName' => 'To',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'recipient@example.com',
            ],
            [
                'name' => 'subject',
                'displayName' => 'Subject',
                'type' => 'string',
                'required' => true,
                'placeholder' => 'Email subject',
            ],
            [
                'name' => 'body',
                'displayName' => 'Body',
                'type' => 'string',
                'required' => true,
                'typeOptions' => [
                    'editor' => 'htmlEditor',
                ],
            ],
            [
                'name' => 'cc',
                'displayName' => 'CC',
                'type' => 'string',
                'placeholder' => 'cc@example.com',
            ],
            [
                'name' => 'bcc',
                'displayName' => 'BCC',
                'type' => 'string',
                'placeholder' => 'bcc@example.com',
            ],
            [
                'name' => 'replyTo',
                'displayName' => 'Reply To',
                'type' => 'string',
                'placeholder' => 'reply@example.com',
            ],
        ];
    }
    
    protected function getCredentials(): array
    {
        return [
            [
                'name' => 'emailSmtp',
                'required' => true,
            ],
        ];
    }
}