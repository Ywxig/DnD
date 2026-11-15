/**
 * Клиентская (JS) валидация формы персонажа.
 *
 * Подсветка полей и вывод сообщений под полями. Правила зеркалят
 * серверную валидацию App\Validators\CharacterValidator — серверная
 * проверка при этом остаётся единственным источником истины.
 */
(function () {
    'use strict';

    var form = document.getElementById('character-form');
    if (!form) {
        return;
    }

    var alignments = (form.getAttribute('data-alignments') || '').split('|').filter(Boolean);

    function isInt(value) {
        return /^-?\d+$/.test(String(value).trim());
    }

    var STAT_RULE = function (value) {
        return isInt(value) && +value >= 3 && +value <= 18
            ? true
            : 'Значение — целое число от 3 до 18.';
    };

    var rules = {
        name: function (value) {
            var length = value.trim().length;
            return length >= 2 && length <= 50
                ? true
                : 'Имя должно содержать от 2 до 50 символов.';
        },
        race_id: function (value) {
            return value !== '' ? true : 'Выберите расу.';
        },
        class_id: function (value) {
            return value !== '' ? true : 'Выберите класс.';
        },
        level: function (value) {
            return isInt(value) && +value >= 1 && +value <= 20
                ? true
                : 'Уровень — целое число от 1 до 20.';
        },
        strength: STAT_RULE,
        dexterity: STAT_RULE,
        constitution: STAT_RULE,
        intelligence: STAT_RULE,
        wisdom: STAT_RULE,
        charisma: STAT_RULE,
        alignment: function (value) {
            return alignments.indexOf(value) !== -1 ? true : 'Выберите мировоззрение.';
        },
        backstory: function (value) {
            return value.length <= 2000 ? true : 'Не более 2000 символов.';
        }
    };

    function fieldOf(name) {
        return form.querySelector('[name="' + name + '"]');
    }

    function errorBoxOf(name) {
        return form.querySelector('[data-error-for="' + name + '"]');
    }

    /**
     * Показывает или убирает ошибку у поля.
     */
    function setFieldState(input, result) {
        var invalid = result !== true;
        input.classList.toggle('is-invalid', invalid);
        var box = errorBoxOf(input.name);
        if (box) {
            box.textContent = invalid ? result : '';
        }
    }

    /**
     * Проверяет одно поле.
     */
    function validateField(input) {
        var rule = rules[input.name];
        if (!rule) {
            return true;
        }
        var result = rule(input.value);
        setFieldState(input, result);
        return result === true;
    }

    /**
     * Проверяет все поля формы.
     */
    function validateForm() {
        var ok = true;
        Object.keys(rules).forEach(function (name) {
            var input = fieldOf(name);
            if (input && !validateField(input)) {
                ok = false;
            }
        });
        return ok;
    }

    form.querySelectorAll('input, select, textarea').forEach(function (input) {
        input.addEventListener('input', function () {
            validateField(input);
        });
        input.addEventListener('change', function () {
            validateField(input);
        });
    });

    form.addEventListener('submit', function (event) {
        if (!validateForm()) {
            event.preventDefault();
            var first = form.querySelector('.is-invalid');
            if (first) {
                first.focus();
            }
        }
    });
})();
