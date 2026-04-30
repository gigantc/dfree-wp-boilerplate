/**
 * iOS-style Toggle Switch
 *
 * Handles accessibility and optional event handling
 * 
 */
document.addEventListener('DOMContentLoaded', () => {
  const toggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');

  // Add ARIA attributes for accessibility
  toggles.forEach(input => {
    input.setAttribute('role', 'switch');
    input.setAttribute('aria-checked', input.checked);
  });

  // Update aria-checked on change
  toggles.forEach(input => {
    input.addEventListener('change', () => {
      input.setAttribute('aria-checked', input.checked);

      // Optional: Dispatch custom event for other scripts to listen to
      input.dispatchEvent(new CustomEvent('toggle:changed', {
        detail: {
          checked: input.checked,
          name: input.name,
          value: input.value
        },
        bubbles: true
      }));
    });

    // Keyboard navigation enhancement
    // Space bar to toggle (already works natively, but this adds smooth animation)
    input.addEventListener('keydown', (e) => {
      if (e.key === ' ' || e.keyCode === 32) {
        e.preventDefault();
        input.checked = !input.checked;
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  });
});
