<?php

// Only the rules the public forms use; anything else falls back to English.
return [
    'required' => 'حقل :attribute مطلوب.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
    'in' => 'قيمة :attribute غير صالحة.',
    'min' => [
        'numeric' => 'يجب ألا يقل :attribute عن :min.',
        'string' => 'يجب ألا يقل :attribute عن :min حرفًا.',
    ],
    'max' => [
        'numeric' => 'يجب ألا يزيد :attribute عن :max.',
        'string' => 'يجب ألا يزيد :attribute عن :max حرفًا.',
    ],

    'attributes' => [
        'profession' => 'المهنة',
        'country' => 'بلد الإقامة',
        'experience_years' => 'سنوات الخبرة',
        'family' => 'من سينتقل',
        'locale' => 'اللغة',
        'message' => 'نص العرض',
        'question' => 'السؤال',
    ],
];
