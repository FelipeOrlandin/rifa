<x-mail::message>
@if($isWinner)
# Parabéns! Você ganhou a rifa! 🎉

Olá {{ $userName }},

<x-mail::panel>
# 🏆 Você é o grande ganhador!

**Rifa:** {{ $rifaTitle }}<br>
**Número sorteado:** {{ str_pad($winningNumber, 3, '0', STR_PAD_LEFT) }}<br>
@if($prizeValue)
**Valor do prêmio:** R$ {{ number_format($prizeValue, 2, ',', '.') }}<br>
@endif
**Data do sorteio:** {{ $drawDate }}
</x-mail::panel>

## Como resgatar seu prêmio

Entre em contato com nosso suporte para mais informações sobre como resgatar seu prêmio.

<x-mail::button :url="mailto:contato@rifaonline.com.br">
Entrar em Contato
</x-mail::button>

@endif

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
