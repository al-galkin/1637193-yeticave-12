<?php
?>
<section class="rates container">
    <h2>Мои ставки</h2>
    <?php if (!empty($bets)): ?>
        <table class="rates__list">
            <?php foreach ($bets as $bet): ?>
                <?php $timer_finished = get_date_range($bet['item_end_time']) ?? '' ?>
                <tr class="rates__item
                <?php if (isset($bet['winner'])) : ?>
                    rates__item--win
                <?php elseif (isset($timer_finished) && ($timer_finished[0] === '00' && $timer_finished[1] === '00')): ?>
                    rates__item--end
                <?php endif; ?>">
                    <td class="rates__info">
                        <div class="rates__img">
                            <img src="../<?= htmlspecialchars($bet['image_url']) ?>" width="54" height="40"
                                 alt="<?= htmlspecialchars($bet['title']) ?>">
                        </div>
                        <div>
                            <h3 class="rates__title"><a
                                    href="/lot.php?id=<?= htmlspecialchars($bet['item_id']); ?>">
                                    <?= htmlspecialchars($bet['title']) ?></a>
                            </h3>
                            <?php if (isset($bet['winner']) && $bet['winner']): ?>
                                <p><?= htmlspecialchars($bet['contacts'] ?? 'Контактов не оставили.') ?></p>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="rates__category">
                        <?= htmlspecialchars($bet['category']) ?>
                    </td>
                    <?php if (isset($bet['winner']) && $bet['winner']): ?>
                        <td class="rates__timer">
                            <div class="timer timer--win">Ставка выиграла</div>
                        </td>
                    <?php elseif (isset($timer_finished) && ($timer_finished[0] === '00' && $timer_finished[1] === '00')): ?>
                        <td class="rates__timer">
                            <div class="timer timer--end">Торги окончены</div>
                        </td>
                    <?php else: ?>
                        <td class="rates__timer">
                            <?php $remaining_time = get_remaining_time($bet['item_end_time']) ?? '' ?>
                            <div
                                class="timer <?php if ($timer_finished[0] === '00'): ?>timer--finishing<?php endif; ?>">
                                <?= $remaining_time ?? '' ?>
                            </div>
                        </td>
                    <?php endif; ?>
                    <td class="rates__price">
                        <?= htmlspecialchars(formatted_sum($bet['current_price'])); ?>
                    </td>
                    <td class="rates__time">
                        <?= get_correct_bet_time($bet['bet_date']); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Ставок еще не было.</p>
    <?php endif; ?>
</section>
