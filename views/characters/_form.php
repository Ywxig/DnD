<?php

/**
 * Общая форма персонажа (создание / редактирование / админ).
 * JS-валидация подключается по id="character-form" (assets/app.js).
 *
 * @var string                $action  URL обработчика формы
 * @var array<string, mixed>  $data    Значения полей
 * @var array<string, string> $errors  Ошибки по полям
 * @var array                 $races   Список рас
 * @var array                 $classes Список классов
 */
use App\Models\Character;
use App\Validators\CharacterValidator;

$stats = Character::STATS;
$statLabels = Character::STAT_LABELS;
?>
<form id="character-form"
      method="post"
      action="<?= $action ?>"
      data-alignments="<?= e(implode('|', CharacterValidator::ALIGNMENTS)) ?>">

    <?= csrf_field() ?>

    <div class="form-row">
        <label for="name">Имя персонажа *</label>
        <input id="name" type="text" name="name" value="<?= e($data['name'] ?? '') ?>"
               class="<?= error_class($errors, 'name') ?>">
        <?= field_error($errors, 'name') ?>
    </div>

    <div class="form-grid">
        <div class="form-row">
            <label for="race_id">Раса *</label>
            <select id="race_id" name="race_id" class="<?= error_class($errors, 'race_id') ?>">
                <option value="">— выберите расу —</option>
                <?php foreach ($races as $race): ?>
                    <option value="<?= (int)$race['id'] ?>"<?= selected_option($data['race_id'] ?? 0, $race['id']) ?>>
                        <?= e($race['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'race_id') ?>
        </div>

        <div class="form-row">
            <label for="class_id">Класс *</label>
            <select id="class_id" name="class_id" class="<?= error_class($errors, 'class_id') ?>">
                <option value="">— выберите класс —</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= (int)$class['id'] ?>"<?= selected_option($data['class_id'] ?? 0, $class['id']) ?>>
                        <?= e($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'class_id') ?>
        </div>

        <div class="form-row">
            <label for="level">Уровень (1–20) *</label>
            <input id="level" type="number" name="level" min="1" max="20"
                   value="<?= e($data['level'] ?? '') ?>"
                   class="<?= error_class($errors, 'level') ?>">
            <?= field_error($errors, 'level') ?>
        </div>

        <div class="form-row">
            <label for="alignment">Мировоззрение *</label>
            <select id="alignment" name="alignment" class="<?= error_class($errors, 'alignment') ?>">
                <option value="">— выберите мировоззрение —</option>
                <?php foreach (CharacterValidator::ALIGNMENTS as $alignment): ?>
                    <option value="<?= e($alignment) ?>"<?= selected_option($data['alignment'] ?? '', $alignment) ?>>
                        <?= e($alignment) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'alignment') ?>
        </div>
    </div>

    <fieldset class="stats-fieldset">
        <legend>Характеристики (3–18) *</legend>
        <div class="form-grid">
            <?php foreach ($stats as $stat): ?>
                <div class="form-row">
                    <label for="<?= e($stat) ?>"><?= e($statLabels[$stat]) ?></label>
                    <input id="<?= e($stat) ?>" type="number" name="<?= e($stat) ?>" min="3" max="18"
                           value="<?= e($data[$stat] ?? '') ?>"
                           class="<?= error_class($errors, $stat) ?>">
                    <?= field_error($errors, $stat) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="form-row">
        <label for="backstory">Предыстория (до 2000 символов)</label>
        <textarea id="backstory" name="backstory" rows="4"
                  class="<?= error_class($errors, 'backstory') ?>"><?= e($data['backstory'] ?? '') ?></textarea>
        <?= field_error($errors, 'backstory') ?>
    </div>

    <button type="submit" class="btn">Сохранить персонажа</button>
</form>
