<?php
/**
 * @var array $ad_information
 * @var string $pagination
 * @var string $search
 */
?>
<div class="container">
    <section class="lots">
        <h2>Результаты поиска по запросу «<span><?= htmlspecialchars($search, ENT_QUOTES | ENT_HTML5) ?? '' ?></span>»
        </h2>
        <ul class="lots__list">
            <?php if (!empty($ad_information)): ?>
                <?php foreach ($ad_information as $ad_value): ?>
                    <li class="lots__item lot">
                        <div class="lot__image">
                            <img src="../<?= htmlspecialchars($ad_value['image_url'] ?? '#', ENT_QUOTES | ENT_HTML5) ?>"
                                 width="350"
                                 height="260"
                                 alt="<?= htmlspecialchars($ad_value['title'] ?? 'Без названия',
                                     ENT_QUOTES | ENT_HTML5) ?>">
                        </div>
                        <div class="lot__info">
                            <span
                                class="lot__category"><?= htmlspecialchars($ad_value['category_title'] ?? 'Без категории',
                                    ENT_QUOTES | ENT_HTML5) ?></span>
                            <h3 class="lot__title"><a class="text-link"
                                                      href="/lot.php?id=<?= htmlspecialchars($ad_value['id'],
                                                          ENT_QUOTES | ENT_HTML5) ?? '' ?>"><?= htmlspecialchars($ad_value['title'] ?? 'Без названия',
                                        ENT_QUOTES | ENT_HTML5) ?></a>
                            </h3>
                            <div class="lot__state">
                                <div class="lot__rate">
                                    <span class="lot__amount">Стартовая цена</span>
                                    <span
                                        class="lot__cost"><?= htmlspecialchars(formatted_sum($ad_value['total'] ?? 0),
                                            ENT_QUOTES | ENT_HTML5) ?? '' ?></span>
                                </div>
                                <?php $timer_finished = get_date_range($ad_value['completed_at'] ?? '') ?>
                                <?php $remaining_time = get_remaining_time($ad_value['completed_at']) ?? '' ?>
                                <div
                                    class="lot__timer timer <?php if ($timer_finished[0] === '00'): ?>timer--finishing<?php endif; ?>">
                                    <?= $remaining_time ?? '' ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>По вашему запросу <b>"<?= htmlspecialchars($search, ENT_QUOTES | ENT_HTML5) ?>"</b> нет подходящих
                    лотов.</p>
            <?php endif; ?>
        </ul>
    </section>
    <?php if (isset($pagination) && !($pagination === '')): ?>
        <?= $pagination ?>
    <?php endif; ?>
</div>
