@props(['field' => '', 'size' => 'normal'])

@if(config('app.settings.captcha') == 'hcaptcha')
<script src="https://js.hcaptcha.com/1/api.js"></script>
@elseif(config('app.settings.captcha') == 'recaptcha2')
<script src="https://www.google.com/recaptcha/api.js"></script>
@endif
<script>
function captcha(e) {
    @this.set('{{$field}}', e)
}
</script>
<div class="overflow-hidden">
    @if(config('app.settings.captcha') == 'hcaptcha')
    <div wire:ignore class="captcha mb-5 h-captcha" data-size="{{ $size }}" data-sitekey="{{ config('app.settings.hcaptcha.site_key') }}" data-callback="captcha"></div>
    @elseif(config('app.settings.captcha') == 'recaptcha2')
    <div wire:ignore class="captcha mb-5 g-recaptcha" data-size="{{ $size }}" data-sitekey="{{ config('app.settings.recaptcha2.site_key') }}" data-callback="captcha"></div>
    @endif
</div>