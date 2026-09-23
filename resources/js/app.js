const menuButton = document.querySelector('.menu');
const navigation = document.querySelector('.nav');

menuButton?.addEventListener('click', () => {
    const isOpen = navigation.classList.toggle('is-open');
    menuButton.setAttribute('aria-expanded', String(isOpen));
});

navigation?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
    navigation.classList.remove('is-open');
    menuButton?.setAttribute('aria-expanded', 'false');
}));

const form = document.querySelector('.form:not(.form--success)');

if (form) {
    const submitButton = form.querySelector('.submit');
    const addPhoneButton = form.querySelector('.add-phone');
    const extraPhones = form.querySelector('.extra-phones');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const touchedFields = new Set();

    const errorElement = (name) => form.querySelector(`[data-error-for="${CSS.escape(name)}"]`);
    const setError = (input, message) => {
        const field = input.closest('.field, .country-code, .check');
        field?.classList.toggle('is-invalid', Boolean(message));
        input.setAttribute('aria-invalid', String(Boolean(message)));

        const error = input.closest('.field-group')?.querySelector('.field-error') ?? errorElement(input.name);
        if (error) {
            error.textContent = message;
        }
    };

    const phoneInputs = () => [...form.querySelectorAll('[name^="phones["][name$="[number]"]')];
    const hasPhone = () => phoneInputs().some((input) => input.value.trim() !== '');
    const isPhoneNumber = (input) => input.name.startsWith('phones[') && input.name.endsWith('[number]');
    const isCountryCode = (input) => input.name.startsWith('phones[') && input.name.endsWith('[country_code]');
    const countryCodeFor = (input) => input.closest('.phone-entry')?.querySelector('[name$="[country_code]"]')?.value ?? '';
    const expectedPhoneLength = (input) => countryCodeFor(input) === '+375' ? 9 : countryCodeFor(input) === '+7' ? 10 : null;

    const validateInput = (input, showError = false) => {
        const value = input.type === 'checkbox' ? input.checked : input.value.trim();
        let message = '';

        if (input.name === 'first_name' && !value) message = 'Imię jest wymagane.';
        if (input.name === 'last_name' && !value) message = 'Nazwisko jest wymagane.';
        if (input.name === 'birth_date' && !value) message = 'Data urodzenia jest wymagana.';
        if (input.name === 'birth_date' && value && new Date(value) > new Date()) message = 'Data urodzenia nie może być datą przyszłą.';
        if (input.name === 'marital_status' && !value) message = 'Wybierz stan cywilny.';
        if (input.name === 'accepted_rules' && !value) message = 'Musisz zaakceptować zasady.';
        if (input.name === 'email' && value && !emailPattern.test(value)) message = 'Podaj prawidłowy adres e-mail.';
        if (input.name === 'about' && value.length > 1000) message = 'Opis nie może mieć więcej niż 1000 znaków.';
        if (isPhoneNumber(input) && value) {
            const expectedLength = expectedPhoneLength(input);
            if (!expectedLength || !new RegExp(`^\\d{${expectedLength}}$`).test(value)) {
                message = expectedLength
                    ? `Numer dla ${countryCodeFor(input)} musi zawierać dokładnie ${expectedLength} cyfr.`
                    : 'Wybierz kod kraju i podaj prawidłowy numer telefonu.';
            }
        }
        if (isPhoneNumber(input) && !value && countryCodeFor(input)) message = 'Podaj numer telefonu.';
        if (isCountryCode(input)) {
            const number = input.closest('.phone-entry')?.querySelector('[name$="[number]"]')?.value.trim();
            if (number && !value) message = 'Wybierz kod kraju.';
        }

        const email = form.querySelector('[name="email"]');
        if ((input.name === 'email' || isPhoneNumber(input)) && !email.value.trim() && !hasPhone()) {
            message = input.name === 'email' ? 'Podaj adres e-mail lub co najmniej jeden numer telefonu.' : 'Podaj numer telefonu lub adres e-mail.';
        }

        if (showError) setError(input, message);
        return message === '';
    };

    const allInputs = () => [...form.querySelectorAll('input:not([type="hidden"]), select, textarea')];
    const updateSubmit = () => {
        submitButton.disabled = !allInputs().every((input) => validateInput(input));
        addPhoneButton.disabled = phoneInputs().length >= 6;
    };

    const bindInput = (input) => {
        const field = input.closest('.field');
        const updateFilled = () => field?.classList.toggle('is-filled', input.value.trim() !== '');
        const validateTouched = () => {
            updateFilled();
            if (touchedFields.has(input)) validateInput(input, true);
            if (input.name === 'email' || isPhoneNumber(input)) {
                const email = form.querySelector('[name="email"]');
                if (touchedFields.has(email)) validateInput(email, true);
            }
            updateSubmit();
        };

        input.addEventListener('input', validateTouched);
        input.addEventListener('change', validateTouched);
        input.addEventListener('blur', () => {
            touchedFields.add(input);
            validateInput(input, true);
        });
        updateFilled();
    };

    allInputs().forEach(bindInput);

    const removePhone = (button) => {
        button.closest('.phone-entry').remove();
        updateSubmit();
    };

    form.querySelectorAll('.remove-phone').forEach((button) => button.addEventListener('click', () => removePhone(button)));

    addPhoneButton.addEventListener('click', () => {
        if (phoneInputs().length >= 6) return;

        const indexes = [...form.querySelectorAll('.phone-entry')].map((entry) => Number(entry.dataset.phoneIndex));
        const index = Math.max(...indexes) + 1;
        const wrapper = document.createElement('div');
        wrapper.className = 'field-group additional-phone phone-entry';
        wrapper.dataset.phoneIndex = String(index);
        wrapper.innerHTML = `<div class="phone-input"><label class="country-code"><span class="sr-only">Kod kraju</span><select name="phones[${index}][country_code]" aria-label="Kod kraju"><option value="">Kod</option><option value="+375">+375</option><option value="+7">+7</option></select></label><label class="field"><span>Dodatkowy telefon</span><input type="tel" name="phones[${index}][number]" maxlength="10" inputmode="numeric" pattern="[0-9]{9,10}"></label></div><button type="button" class="remove-phone" aria-label="Usuń numer">×</button><p class="field-error" data-error-for="phones.${index}.number"></p>`;
        const input = wrapper.querySelector('[name$="[number]"]');
        wrapper.querySelector('.remove-phone').addEventListener('click', (event) => removePhone(event.currentTarget));
        extraPhones.append(wrapper);
        wrapper.querySelectorAll('input, select').forEach(bindInput);
        input.focus();
        updateSubmit();
    });

    const updatePhoneConstraints = (entry) => {
        const input = entry.querySelector('[name$="[number]"]');
        const expectedLength = expectedPhoneLength(input);
        input.maxLength = expectedLength ?? 10;
        input.pattern = expectedLength ? `[0-9]{${expectedLength}}` : '[0-9]{9,10}';
        if (touchedFields.has(input)) validateInput(input, true);
    };

    form.addEventListener('change', (event) => {
        if (isCountryCode(event.target)) {
            updatePhoneConstraints(event.target.closest('.phone-entry'));
            updateSubmit();
        }
    });

    form.querySelectorAll('.phone-entry').forEach(updatePhoneConstraints);

    form.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target.tagName !== 'TEXTAREA') {
            event.preventDefault();
        }
    });

    form.addEventListener('submit', (event) => {
        let isValid = true;
        allInputs().forEach((input) => {
            touchedFields.add(input);
            if (!validateInput(input, true)) isValid = false;
        });

        if (!isValid) {
            event.preventDefault();
            form.querySelector('[aria-invalid="true"]')?.focus();
        }
    });

    updateSubmit();
}
