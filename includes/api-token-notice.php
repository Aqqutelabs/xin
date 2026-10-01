<?php
$newApiToken = $_SESSION['new_api_token'] ?? null;
unset($_SESSION['new_api_token']);
if ($newApiToken !== null):
?>
<section class="notice success" aria-labelledby="new-api-token-title">
  <strong id="new-api-token-title">Your API token</strong>
  <p>Copy this token now. It will only be shown once.</p>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <input id="new-api-token" type="text" value="<?= e($newApiToken) ?>" readonly aria-label="API token" style="min-width:min(100%,360px);flex:1">
    <button class="primary-btn" id="copy-api-token" type="button">Copy token</button>
  </div>
  <span id="api-token-copy-status" role="status"></span>
</section>
<script>
const apiTokenInput = document.getElementById('new-api-token');
if (apiTokenInput && apiTokenInput.value) {
  console.log('New API token:', apiTokenInput.value);
}

document.getElementById('copy-api-token')?.addEventListener('click', async function () {
  const input = document.getElementById('new-api-token');
  const status = document.getElementById('api-token-copy-status');
  try {
    await navigator.clipboard.writeText(input.value);
    status.textContent = 'Copied';
  } catch (error) {
    input.select();
    status.textContent = document.execCommand('copy') ? 'Copied' : 'Select and copy the token';
  }
});
</script>
<?php endif; ?>