<?php

return [
    'required' => 'Bidang :attribute wajib diisi.',
    'email' => 'Bidang :attribute harus berupa alamat email yang valid.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'unique' => ':Attribute sudah terdaftar sebelumnya, silakan gunakan yang lain.',
    
    'min' => [
        'string' => 'Bidang :attribute minimal harus berisi :min karakter.',
    ],
    
    'password' => [
        'letters' => 'Bidang :attribute harus berisi setidaknya satu huruf.',
        'mixed' => 'Bidang :attribute harus berisi setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => 'Bidang :attribute harus berisi setidaknya satu angka.',
        'symbols' => 'Bidang :attribute harus berisi setidaknya satu simbol.',
        'uncompromised' => ':attribute yang diberikan telah muncul dalam kebocoran data. Silakan pilih :attribute yang berbeda.',
    ],
    
    'attributes' => [
        'name' => 'nama lengkap',
        'username' => 'username',
        'email' => 'email',
        'password' => 'password',
        'password_confirmation' => 'konfirmasi password',
    ],
];
