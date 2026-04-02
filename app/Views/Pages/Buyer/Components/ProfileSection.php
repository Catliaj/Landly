<?php
$buyerProfile = $buyerProfile ?? [];
$profileStats = $buyerProfile['stats'] ?? [];
$fullName = trim((string) ($buyerProfile['full_name'] ?? 'Buyer'));
$fullName = $fullName !== '' ? $fullName : 'Buyer';
$firstName = trim((string) ($buyerProfile['first_name'] ?? ''));
$lastName = trim((string) ($buyerProfile['last_name'] ?? ''));
$email = trim((string) ($buyerProfile['email'] ?? ''));
$avatarUrl = trim((string) ($buyerProfile['avatar_url'] ?? ''));
$initials = trim((string) ($buyerProfile['initials'] ?? 'NA'));
$profileActionUrl = base_url('buyer/profile');
$profileMessage = session()->getFlashdata('buyer_profile_message');
$profileError = session()->getFlashdata('buyer_profile_error');
?>
<?php if ($profileMessage !== null): ?>
    <div style="margin-bottom: 18px; padding: 12px 16px; border-radius: 12px; background: rgba(64, 145, 108, 0.16); border: 1px solid rgba(64, 145, 108, 0.35); color: #dff6e7;">
        <?= esc((string) $profileMessage) ?>
    </div>
<?php endif; ?>
<?php if ($profileError !== null): ?>
    <div style="margin-bottom: 18px; padding: 12px 16px; border-radius: 12px; background: rgba(214, 40, 40, 0.16); border: 1px solid rgba(214, 40, 40, 0.35); color: #ffd7d7;">
        <?= esc((string) $profileError) ?>
    </div>
<?php endif; ?>
<section id="section-profile" class="content-section">
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-avatar">
                <?php if ($avatarUrl !== ''): ?>
                    <img src="<?= esc($avatarUrl) ?>" alt="<?= esc($fullName) ?>">
                <?php else: ?>
                    <?= esc($initials !== '' ? $initials : 'NA') ?>
                <?php endif; ?>
            </div>
            <h3 class="profile-name"><?= esc($fullName) ?></h3>
            <p class="profile-email"><?= esc($email !== '' ? $email : 'No email available') ?></p>
            <div class="profile-stats">
                <div class="profile-stat">
                    <div class="profile-stat-value"><?= esc((string) ($profileStats['saved_properties'] ?? 0)) ?></div>
                    <div class="profile-stat-label">Saved</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value"><?= esc((string) ($profileStats['accepted_inquiries'] ?? 0)) ?></div>
                    <div class="profile-stat-label">Inquiries</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value"><?= esc((string) ($profileStats['unread_messages'] ?? 0)) ?></div>
                    <div class="profile-stat-label">Messages</div>
                </div>
            </div>
        </div>

        <form class="profile-settings" action="<?= esc($profileActionUrl) ?>" method="post">
            <div class="settings-section">
                <h3>Personal Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="buyer-first-name">First Name</label>
                        <input id="buyer-first-name" name="first_name" type="text" class="form-control" value="<?= esc($firstName) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="buyer-last-name">Last Name</label>
                        <input id="buyer-last-name" name="last_name" type="text" class="form-control" value="<?= esc($lastName) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="buyer-email">Email Address</label>
                    <input id="buyer-email" name="email" type="email" class="form-control" value="<?= esc($email) ?>" required>
                </div>
                <p style="margin: 0 0 18px; color: rgba(254, 250, 224, 0.65); font-size: 0.85rem;">This profile is backed by the existing users table only.</p>
                <div class="settings-actions">
                    <button class="btn-secondary" type="reset">Cancel</button>
                    <button class="btn-primary" type="submit" name="profile_action" value="save_profile">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</section>