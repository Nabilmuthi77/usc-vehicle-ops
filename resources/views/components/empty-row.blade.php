{{-- Baris kosong seragam untuk seluruh tabel daftar. --}}
@props(['colspan' => 5, 'message' => 'Belum ada data.'])

<tr>
    <td colspan="{{ $colspan }}" class="px-5 py-10 text-center text-sm text-gray-500">
        {{ $message }}
    </td>
</tr>
