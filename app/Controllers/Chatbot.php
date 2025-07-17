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