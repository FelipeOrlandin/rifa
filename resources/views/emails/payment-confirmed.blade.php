<x-mail::message>
# Pagamento Confirmado!

Olá {{ $userName }},

Seus números da rifa foram confirmados com sucesso!

<x-mail::table>
| Rifa | Números | Valor |
|:-----|:--------|------:|
| {{ $rifaTitle }} | {{ implode(', ', array_map(fn($n) => str_pad($n, 3, '0', STR_PAD_LEFT), $numbers)) }} | R$ {{ number_format($totalAmount, 2, ',', '.') }} |
</x-mail::table>

**Data da compra:** {{ $paymentDate }}

---

## Boa sorte no sorteio! 🍀

Os seus números estão oficialmente registrados. Fique de olho na data do sorteio!

<x-mail::button :url="route('rifas')">
Ver Rifas Disponíveis
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
