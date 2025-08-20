<script>
(function() {
  function toUppercaseInput(e) {
    const el = e.target;
    if (!el) return;
    const tag = (el.tagName || '').toLowerCase();
    const type = (el.type || '').toLowerCase();
    // Apply to text-like inputs and textareas (exclude sensitive or special formats)
    const isTextLike = tag === 'textarea' || (tag === 'input' && ['text','search','tel'].includes(type));
    if (!isTextLike) return;

    // Skip inputs with explicit opt-out
    if (el.hasAttribute('data-skip-uppercase')) return;

    // Keep caret position when transforming
    const start = el.selectionStart;
    const end = el.selectionEnd;

    const newValue = el.value?.toString().toUpperCase();
    if (newValue !== undefined && newValue !== el.value) {
      el.value = newValue;
      try {
        if (start !== null && end !== null) {
          el.setSelectionRange(start, end);
        }
      } catch (_) {}

      // Trigger input event so Alpine/Livewire/Filament pick changes
      const evt = new Event('input', { bubbles: true });
      el.dispatchEvent(evt);
    }
  }

  // Event delegation for better performance
  document.addEventListener('input', toUppercaseInput, true);
})();
</script>
<style>
/* Visual hint (optional): make all text uppercase by CSS too */
input[type="text"], input[type="search"], input[type="tel"], textarea {
  text-transform: uppercase;
}
</style>
