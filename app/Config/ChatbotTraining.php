<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ChatbotTraining extends BaseConfig
{
    public array $intents = [
        [
            'name' => 'greeting',
            'examples' => [
                'hi',
                'hello',
                'hey',
                'hai',
                'kumusta',
                'kamusta',
            ],
            'reply_type' => 'greeting',
        ],
        [
            'name' => 'availability',
            'examples' => [
                'what are the available land listing',
                'show available land listings',
                'available properties in nasugbu',
                'list available lots',
                'what land is available',
                'available lot near school',
                'what are the available residential category',
                'show available residential category',
                'list available residential category',
                'available residential category in nasugbu',
            ],
            'reply_type' => 'availability',
        ],
        [
            'name' => 'near_school',
            'examples' => [
                'near school',
                'near elementary school',
                'near secondary school',
                'land near school',
                'property close to school',
                'school nearby',
                'near an educational institution',
                'close to academic area',
            ],
            'reply_type' => 'recommendation',
        ],
        [
            'name' => 'near_beach',
            'examples' => [
                'near beach',
                'land near beach',
                'property close to beach',
                'beach front land',
                'beachfront property',
                'near the beach',
                'coastal property',
                'by the beach',
            ],
            'reply_type' => 'recommendation',
        ],
        [
            'name' => 'near_church',
            'examples' => [
                'near church',
                'land near church',
                'property close to church',
                'near place of worship',
                'near the chapel',
                'church vicinity',
                'close to religious site',
            ],
            'reply_type' => 'recommendation',
        ],
        [
            'name' => 'near_barangay',
            'examples' => [
                'near barangay',
                'near brgy',
                'land near brgy hall',
                'property near barangay center',
            ],
            'reply_type' => 'recommendation',
        ],
        [
            'name' => 'budget_query',
            'examples' => [
                'budget under php 500k',
                'budget under ₱500,000',
                'budget 1m pesos',
                'between ₱500,000 and ₱1,000,000',
                'cheap land in nasugbu',
                'land for 500k pesos',
            ],
            'reply_type' => 'recommendation',
        ],
        [
            'name' => 'nasugbu_barangay',
            'examples' => [
                'mataas na parang',
                'brgy mataasnparang',
                'barangay alalaken',
                'barangay gulod',
                'brgy gatas',
                'barangay ilalim',
                'barangay cogtong',
                'barangay cotta',
                'barangay pamilacan',
                'brgy playa hermosa',
                'barangay sapunggan',
                'brgy talugtug',
                'barangay tamis',
                'barangay pooc',
                'brgy sampaguita',
                'barangay subic',
                'barangay bagalangit',
                'barangay busiing',
                'land in gulod',
                'property at alalaken',
                'lot near cogtong',
            ],
            'reply_type' => 'recommendation',
        ],
        [
            'name' => 'clarify',
            'examples' => [
                'find me a land',
                'help me choose',
                'recommend a property',
                'i want to buy land',
            ],
            'reply_type' => 'clarify',
        ],
    ];
}
