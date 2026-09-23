<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enemer — logistyka dla e-commerce</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="header">
    <div class="container header__inner">
        <a href="#"><img class="logo" src="{{ asset('images/enemer/logo-header.svg') }}" alt="Enemer"></a>
        <button class="menu" type="button" aria-expanded="false" aria-controls="nav"><span></span><span></span><span></span><b class="sr-only">Menu</b></button>
        <nav id="nav" class="nav">
            <a href="#about">O nas</a><a href="#services">Usługi</a><a href="#pricing">Cennik</a><a href="#contact">Kontakt</a><a href="#reviews">Opinie</a><a href="#faq">Pytania i odpowiedzi</a>
        </nav>
        <div class="phone"><a href="tel:+48690590089">+48690590089</a><a href="#contact">Zamów rozmowę</a></div>
        <button class="language" type="button">PL <img src="{{ asset('images/enemer/chevron-dark.svg') }}" alt=""></button>
    </div>
</header>

<main>
    <section class="couriers">
        <div class="couriers__inner">
            <div class="couriers__list">
                <h1>Nasi kurierzy</h1>
                @php
                    $couriers = [
                        ['dpd.png', 'DPD'], ['gls.png', 'GLS'], ['dhl.png', 'DHL'],
                        ['shopify.png', 'Shopify'], ['woocommerce.png', 'WooCommerce'], ['prestashop.png', 'PrestaShop'],
                        ['eppl.png', 'EPPL'], ['poczta.png', 'Poczta Polska'], ['magento.png', 'Magento'],
                    ];
                @endphp
                <div class="courier-grid">
                    @foreach ($couriers as [$image, $name])
                        <article class="courier-card"><img src="{{ asset('images/enemer/'.$image) }}" alt="{{ $name }}"></article>
                    @endforeach
                </div>
            </div>
            <img class="packing" src="{{ asset('images/enemer/packing.png') }}" alt="Przygotowanie paczki do wysyłki">
        </div>
    </section>

    <section id="contact" class="contact">
        <img class="contact__background" src="{{ asset('images/enemer/warehouse.png') }}" alt="Magazyn logistyczny">
        <div class="container contact__inner">
            @if (session('success'))
                <div class="form form--success" role="status">
                    <h2>Udało się!</h2>
                    <p>Pomyślnie</p>
                </div>
            @else
            <form class="form" method="POST" action="{{ route('applications.store') }}" novalidate>
                @csrf
                <div class="form__heading"><h2>Szukasz najlepszej oferty?</h2><p>Zostaw aplikację, a nasz menedżer skontaktuje się z Tobą w celu konsultacji</p></div>
                <div class="form__row">
                    <div class="field-group"><label class="field @error('first_name') is-invalid @enderror"><span>Twoje imię</span><input name="first_name" maxlength="100" value="{{ old('first_name') }}" required></label><p class="field-error" data-error-for="first_name">@error('first_name'){{ $message }}@enderror</p></div>
                    <div class="field-group"><label class="field @error('last_name') is-invalid @enderror"><span>Twoje nazwisko</span><input name="last_name" maxlength="100" value="{{ old('last_name') }}" required></label><p class="field-error" data-error-for="last_name">@error('last_name'){{ $message }}@enderror</p></div>
                    <div class="field-group"><label class="field @error('middle_name') is-invalid @enderror"><span>Twoje drugie imię</span><input name="middle_name" maxlength="100" value="{{ old('middle_name') }}"></label><p class="field-error" data-error-for="middle_name">@error('middle_name'){{ $message }}@enderror</p></div>
                </div>
                <div class="field-group"><label class="field field--date @error('birth_date') is-invalid @enderror"><span>Twoja data urodzenia</span><input type="date" name="birth_date" value="{{ old('birth_date') }}" max="{{ now()->toDateString() }}" required></label><p class="field-error" data-error-for="birth_date">@error('birth_date'){{ $message }}@enderror</p></div>
                <div class="field-group"><label class="field @error('email') is-invalid @enderror"><span>E-mail</span><input type="email" name="email" maxlength="255" value="{{ old('email') }}"></label><p class="field-error" data-error-for="email">@error('email'){{ $message }}@enderror</p></div>
                <div class="phone-field field-group">
                    <div class="phone-input">
                        <label class="country-code @error('country_code') is-invalid @enderror"><span class="sr-only">Kod kraju</span><select name="country_code" aria-label="Kod kraju"><option value="+48" selected>+48</option></select></label>
                        <label class="field @error('phone_numbers.0') is-invalid @enderror"><span>Telefon</span><input type="tel" name="phone_numbers[]" value="{{ old('phone_numbers.0') }}" inputmode="tel" maxlength="20"></label>
                    </div>
                    <button class="add-phone" type="button"><span>Dodaj kolejny numer</span><img src="{{ asset('images/enemer/plus.svg') }}" alt=""></button>
                    <p class="field-error" data-error-for="phone_numbers.0">@error('country_code'){{ $message }} @enderror @error('phone_numbers.0'){{ $message }}@enderror</p>
                </div>
                <div class="extra-phones">
                    @foreach (array_slice(old('phone_numbers', []), 1) as $index => $phoneNumber)
                        <div class="field-group additional-phone"><label class="field @error('phone_numbers.'.($index + 1)) is-invalid @enderror"><span>Dodatkowy telefon</span><input type="tel" name="phone_numbers[]" value="{{ $phoneNumber }}" maxlength="20"></label><button type="button" class="remove-phone" aria-label="Usuń numer">×</button><p class="field-error">@error('phone_numbers.'.($index + 1)){{ $message }}@enderror</p></div>
                    @endforeach
                </div>
                <div class="field-group"><label class="field select-native @error('marital_status') is-invalid @enderror"><span class="sr-only">Stan cywilny</span><select name="marital_status" required><option value="">Stan cywilny</option><option value="single" @selected(old('marital_status') === 'single')>Holost/niezamężna</option><option value="married" @selected(old('marital_status') === 'married')>Żonaty/zamężna</option><option value="divorced" @selected(old('marital_status') === 'divorced')>Rozwiedziony/rozwiedziona</option><option value="widowed" @selected(old('marital_status') === 'widowed')>Wdowiec/wdowa</option></select></label><p class="field-error" data-error-for="marital_status">@error('marital_status'){{ $message }}@enderror</p></div>
                <div class="field-group"><label class="field field--about @error('about') is-invalid @enderror"><span>O mnie</span><textarea name="about" maxlength="1000" rows="1">{{ old('about') }}</textarea></label><p class="field-error" data-error-for="about">@error('about'){{ $message }}@enderror</p></div>
                <div class="form__footer">
                    <div><label class="check @error('accepted_rules') is-invalid @enderror"><input type="checkbox" name="accepted_rules" value="1" @checked(old('accepted_rules')) required><i></i><span>Przeczytałem zasady</span></label><p class="field-error" data-error-for="accepted_rules">@error('accepted_rules'){{ $message }}@enderror</p></div>
                    <button class="submit" type="submit" disabled>Wysłać</button>
                </div>
                <p class="form__message" role="status"></p>
            </form>
            @endif
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer__columns">
            <div class="footer__contact">
                <img class="footer__logo" src="{{ asset('images/enemer/logo-footer.svg') }}" alt="Enemer">
                <div class="footer__contacts">
                    <p><img src="{{ asset('images/enemer/phone.svg') }}" alt=""><a href="tel:+48690590089">+48690590089</a></p>
                    <a class="purple" href="#contact">Zamów rozmowę</a>
                    <p><img src="{{ asset('images/enemer/email.svg') }}" alt=""><a href="mailto:info@enemer.pl">info@enemer.pl</a></p>
                    <p><img src="{{ asset('images/enemer/location.svg') }}" alt=""><span>Błonie, Pass 20I, budynek 15,<br>05-870</span></p>
                </div>
            </div>
            <div id="services" class="footer__links"><strong>Usługi</strong><a href="#">Usługi logistyczne dla e-commerce</a><a href="#">Outsourcing magazynu</a><a href="#">Outsourcing logistyczny</a><a href="#">Obsługa logistyczna sklepów internetowych</a><a href="#">Logistyka kontraktowa</a><a class="purple all" href="#">Zobacz wszystkie <img src="{{ asset('images/enemer/arrow.svg') }}" alt=""></a></div>
            <nav class="footer__nav"><a id="about" href="#">O nas</a><a id="pricing" href="#">Cennik</a><a id="faq" href="#">Pytania i odpowiedzi</a><a href="#contact">Kontakt</a><a href="#">Blog</a></nav>
            <div class="footer__legal"><p>Space Logistics Sp.z.o.o. 02-727</p><p>Warszawa ul. Wołodyjowskiego 67A</p><p>KRS: 0000824771 NIP: 5213888029</p><p>REGON: 385377605</p></div>
        </div>
        <div class="footer__bottom"><a href="#">Polityka prywatności</a><div><span>dev.grizzly.by</span><img src="{{ asset('images/enemer/grizzly.svg') }}" alt="Grizzly"><span>seo.grizzly.by</span></div></div>
    </div>
</footer>
</body>
</html>
