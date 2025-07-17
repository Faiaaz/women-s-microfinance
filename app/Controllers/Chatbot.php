<?php

namespace App\Controllers;

class Chatbot extends BaseController
{
    public function index(): string
    {
        return view('chatbot/index');
    }

    public function processMessage()
    {
        $message = $this->request->getPost('message');
        $step = $this->request->getPost('step') ?? 1;
        
        $response = $this->getBotResponse($message, $step);
        
        return $this->response->setJSON([
            'response' => $response['message'],
            'next_step' => $response['next_step'],
            'options' => $response['options'] ?? [],
            'finished' => $response['finished'] ?? false
        ]);
    }

    // Facebook Messenger Webhook Verification
    public function webhook()
    {
        $mode = $this->request->getGet('hub_mode');
        $token = $this->request->getGet('hub_verify_token');
        $challenge = $this->request->getGet('hub_challenge');

        // Use the verify token we discussed
        $verify_token = 'shakti_webhook_2024';

        // Debug logging
        $log_file = WRITEPATH . 'logs/facebook_webhook.log';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - WEBHOOK VERIFICATION ATTEMPT\n", FILE_APPEND);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Mode: " . $mode . "\n", FILE_APPEND);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Token: " . $token . "\n", FILE_APPEND);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Challenge: " . $challenge . "\n", FILE_APPEND);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Expected Token: " . $verify_token . "\n", FILE_APPEND);

        if ($mode === 'subscribe' && $token === $verify_token) {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - VERIFICATION SUCCESSFUL\n", FILE_APPEND);
            return $this->response->setBody($challenge);
        }

        file_put_contents($log_file, date('Y-m-d H:i:s') . " - VERIFICATION FAILED\n", FILE_APPEND);
        return $this->response->setStatusCode(403);
    }

    // Facebook Messenger Message Handler
    public function receiveMessage()
    {
        // Simple test response to see if Facebook is reaching us
        $log_file = WRITEPATH . 'logs/facebook_webhook.log';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - WEBHOOK HIT!\n", FILE_APPEND);
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Debug logging
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Received webhook: " . json_encode($input) . "\n", FILE_APPEND);

        if (isset($input['entry'][0]['messaging'])) {
            $messaging = $input['entry'][0]['messaging'][0];
            $sender_id = $messaging['sender']['id'];
            
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Processing message from: " . $sender_id . "\n", FILE_APPEND);
            
            if (isset($messaging['message']['text'])) {
                $message = $messaging['message']['text'];
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - Message text: " . $message . "\n", FILE_APPEND);
                $this->handleFacebookMessage($sender_id, $message);
            } else {
                file_put_contents($log_file, date('Y-m-d H:i:s') . " - No text message found\n", FILE_APPEND);
            }
        } else {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - No messaging data found\n", FILE_APPEND);
        }

        return $this->response->setBody('OK');
    }

    // Test endpoint to verify webhook is working
    public function test()
    {
        // Log this test request
        $log_file = WRITEPATH . 'logs/facebook_webhook.log';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - TEST ENDPOINT HIT\n", FILE_APPEND);
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Webhook endpoint is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'webhook_url' => 'https://web-production-66a8.up.railway.app/facebook/webhook',
            'verify_token' => 'shakti_webhook_2024'
        ]);
    }

    // Test endpoint to simulate Facebook message
    public function testMessage()
    {
        // Simulate a Facebook message
        $test_sender_id = '123456789';
        $test_message = 'Hello';
        
        $this->handleFacebookMessage($test_sender_id, $test_message);
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Test message processed',
            'sender_id' => $test_sender_id,
            'message_text' => $test_message
        ]);
    }

    // Check Facebook webhook logs
    public function checkLogs()
    {
        $log_file = WRITEPATH . 'logs/facebook_webhook.log';
        
        if (file_exists($log_file)) {
            $logs = file_get_contents($log_file);
            return $this->response->setJSON([
                'status' => 'success',
                'logs' => $logs,
                'file_size' => filesize($log_file),
                'last_modified' => date('Y-m-d H:i:s', filemtime($log_file))
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Log file does not exist',
                'log_file_path' => $log_file
            ]);
        }
    }

    private function handleFacebookMessage($sender_id, $message)
    {
        // Get or create user session
        $session = $this->getUserSession($sender_id);
        $step = $session['step'] ?? 1;
        
        $response = $this->getBotResponse($message, $step);
        
        // Send response back to Facebook
        $this->sendFacebookMessage($sender_id, $response['message']);
        
        // Update user session
        $this->updateUserSession($sender_id, $response['next_step']);
        
        // Send quick replies if available
        if (isset($response['options']) && !empty($response['options'])) {
            $this->sendQuickReplies($sender_id, $response['options']);
        }
    }

    private function sendFacebookMessage($recipient_id, $message)
    {
        $page_access_token = 'EAAPKDQxpu94BPH2wUO5lm7b6U1FDtJhGS3E9lagdJZBwoXYnnLdIQiXn82wewy8PY4zZC7666mLbk9S5NRZB0wZAEOxSubv3QW40hqWiJpOC97w0AOlWC444bQpFKxyH1SBoLgKuAjcAaci7JM4quYQp8GYDPM0V0HsJB4JPvNp9UelmTUyPDhgdzVsr2CkvOwtUyI40aAZDZD';
        
        $data = [
            'recipient' => ['id' => $recipient_id],
            'message' => ['text' => $message]
        ];

        $result = $this->callFacebookAPI($data, $page_access_token);
        
        // Debug logging
        $log_file = WRITEPATH . 'logs/facebook_webhook.log';
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Sending message to " . $recipient_id . ": " . $message . "\n", FILE_APPEND);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - API Response: " . $result . "\n", FILE_APPEND);
        
        return $result;
    }

    private function sendQuickReplies($recipient_id, $options)
    {
        $page_access_token = 'EAAPKDQxpu94BPH2wUO5lm7b6U1FDtJhGS3E9lagdJZBwoXYnnLdIQiXn82wewy8PY4zZC7666mLbk9S5NRZB0wZAEOxSubv3QW40hqWiJpOC97w0AOlWC444bQpFKxyH1SBoLgKuAjcAaci7JM4quYQp8GYDPM0V0HsJB4JPvNp9UelmTUyPDhgdzVsr2CkvOwtUyI40aAZDZD';
        
        $quick_replies = [];
        foreach ($options as $option) {
            $quick_replies[] = [
                'content_type' => 'text',
                'title' => $option,
                'payload' => $option
            ];
        }

        $data = [
            'recipient' => ['id' => $recipient_id],
            'message' => [
                'text' => 'Please choose an option:',
                'quick_replies' => $quick_replies
            ]
        ];

        $this->callFacebookAPI($data, $page_access_token);
    }

    private function callFacebookAPI($data, $page_access_token)
    {
        $url = "https://graph.facebook.com/v18.0/me/messages?access_token=" . $page_access_token;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return $result;
    }

    private function getUserSession($sender_id)
    {
        // Simple file-based session storage (in production, use a database)
        $session_file = WRITEPATH . 'sessions/' . $sender_id . '.json';
        
        if (file_exists($session_file)) {
            return json_decode(file_get_contents($session_file), true);
        }
        
        return ['step' => 1];
    }

    private function updateUserSession($sender_id, $step)
    {
        $session_file = WRITEPATH . 'sessions/' . $sender_id . '.json';
        $session_dir = dirname($session_file);
        
        if (!is_dir($session_dir)) {
            mkdir($session_dir, 0755, true);
        }
        
        file_put_contents($session_file, json_encode(['step' => $step]));
    }

    private function getBotResponse($message, $step)
    {
        switch ($step) {
            case 1:
                return [
                    'message' => "Hello! Welcome to Women's Empowerment Microfinance. I'm here to help you with loan information and applications. How can I assist you today?",
                    'next_step' => 2,
                    'options' => [
                        'I want to apply for a loan',
                        'Tell me about your loan programs',
                        'What documents do I need?',
                        'I have questions about repayment'
                    ]
                ];
                
            case 2:
                $message = strtolower($message);
                
                // Handle specific document-related responses
                if (strpos($message, 'need help getting') !== false || strpos($message, 'help getting') !== false) {
                    return [
                        'message' => "No worries! We understand that getting documents can be challenging. Here's how we can help:\n\n• **ID Documents**: We accept expired IDs up to 6 months past expiry\n• **Income Proof**: We can work with verbal income verification for small amounts\n• **Bank Statements**: We accept mobile money statements or cash deposit records\n• **References**: Family members or community leaders can serve as references\n• **Business Plan**: We can help you create a simple business plan\n\nWould you like to speak with a loan officer who can guide you through getting these documents?",
                        'next_step' => 5
                    ];
                } elseif (strpos($message, 'yes, i have them') !== false || strpos($message, 'have them') !== false) {
                    return [
                        'message' => "Excellent! Since you have your documents ready, let's get started with your loan application. Could you tell me a bit about your business or income source?",
                        'next_step' => 3
                    ];
                } elseif (strpos($message, 'apply anyway') !== false || strpos($message, 'apply without') !== false) {
                    return [
                        'message' => "I understand you want to proceed with the application. We can start the process and work on getting the documents together. Could you tell me a bit about your business or income source?",
                        'next_step' => 3
                    ];
                } elseif (strpos($message, 'apply') !== false || strpos($message, 'loan') !== false) {
                    return [
                        'message' => "Great! I'd be happy to help you apply for a loan. To get started, could you tell me a bit about your business or income source?",
                        'next_step' => 3
                    ];
                } elseif (strpos($message, 'program') !== false || strpos($message, 'information') !== false) {
                    return [
                        'message' => "We offer several loan programs designed specifically for women entrepreneurs:\n\n• Small Business Loans: $500 - $5,000\n• Emergency Loans: $200 - $1,000\n• Education Loans: $1,000 - $3,000\n\nAll loans have flexible repayment terms and low interest rates. Would you like to apply?",
                        'next_step' => 2,
                        'options' => [
                            'Yes, I want to apply',
                            'Tell me more about requirements',
                            'What are the interest rates?'
                        ]
                    ];
                } elseif (strpos($message, 'document') !== false) {
                    return [
                        'message' => "For loan applications, you'll need:\n\n• Valid ID (passport, driver's license)\n• Proof of income (pay stubs, business records)\n• Bank statements (last 3 months)\n• Two references\n• Business plan (for business loans)\n\nDo you have these documents ready?",
                        'next_step' => 2,
                        'options' => [
                            'Yes, I have them',
                            'I need help getting some documents',
                            'Let me apply anyway'
                        ]
                    ];
                } elseif (strpos($message, 'repayment') !== false) {
                    return [
                        'message' => "Our repayment terms are very flexible:\n\n• Weekly, bi-weekly, or monthly payments\n• Grace period of 2 weeks for emergencies\n• No penalties for early repayment\n• We work with you if you face difficulties\n\nWould you like to speak with someone about your specific situation?",
                        'next_step' => 5
                    ];
                } else {
                    return [
                        'message' => "I understand you're interested in our services. Let me connect you with one of our loan officers who can provide personalized assistance.",
                        'next_step' => 5
                    ];
                }
                
            case 3:
                return [
                    'message' => "Thank you for sharing that information. Now, could you tell me approximately how much you're looking to borrow? This will help me guide you to the right loan program.",
                    'next_step' => 4
                ];
                
            case 4:
                $amount = preg_replace('/[^0-9]/', '', $message);
                if ($amount >= 500 && $amount <= 5000) {
                    $loanType = "Small Business Loan";
                } elseif ($amount >= 200 && $amount < 500) {
                    $loanType = "Emergency Loan";
                } elseif ($amount >= 1000 && $amount <= 3000) {
                    $loanType = "Education Loan";
                } else {
                    $loanType = "Custom Loan";
                }
                
                return [
                    'message' => "Perfect! Based on the amount you mentioned, I'd recommend our $loanType program. I have all the information I need to help you get started. Let me connect you with one of our experienced loan officers who will guide you through the application process and answer any specific questions you may have.",
                    'next_step' => 5,
                    'finished' => true
                ];
                
            case 5:
                return [
                    'message' => "I'm connecting you with one of our loan officers now. They will call you within the next 24 hours to discuss your application and answer any questions. Thank you for choosing Women's Empowerment Microfinance! 🌟",
                    'next_step' => 1,
                    'finished' => true
                ];
                
            default:
                return [
                    'message' => "I apologize for the confusion. Let me connect you with one of our staff members who can better assist you.",
                    'next_step' => 5,
                    'finished' => true
                ];
        }
    }
} 