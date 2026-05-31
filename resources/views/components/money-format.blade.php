<script>
// Live Rupiah formatting: thousands separator (.) inserted AS YOU TYPE.
(function() {
  function toRaw(val)  { return (val || '').replace(/[^0-9]/g, ''); }
  function fmt(raw)    { return raw ? parseInt(raw, 10).toLocaleString('id-ID') : ''; }

  function handleInput(el) {
    var before    = el.value;
    var caret     = el.selectionStart;
    var digitsLeft = toRaw(before.slice(0, caret)).length;   // digits before caret
    var formatted = fmt(toRaw(before));
    el.value = formatted;
    // Restore caret after the same number of digits
    var pos = 0, seen = 0;
    while (pos < formatted.length && seen < digitsLeft) {
      if (/[0-9]/.test(formatted[pos])) seen++;
      pos++;
    }
    try { el.setSelectionRange(pos, pos); } catch (e) {}
  }

  function attach(el) {
    if (el.dataset.rpBound) return;
    el.dataset.rpBound = '1';
    if (el.value) el.value = fmt(toRaw(el.value));      // format pre-filled value
    el.addEventListener('input', function() { handleInput(this); });
  }

  function attachAll(root) {
    (root || document).querySelectorAll('.money-input').forEach(attach);
  }

  attachAll(document);
  // Re-bind for content added later (e.g. Livewire morphs, dynamically inserted rows)
  document.addEventListener('livewire:navigated', function() { attachAll(document); });

  // Before submit: convert all money inputs back to raw digits for the server.
  document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function() {
      form.querySelectorAll('.money-input').forEach(function(el) { el.value = toRaw(el.value); });
    });
  });
})();
</script>
