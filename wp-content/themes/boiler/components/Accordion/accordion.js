(() => {
	'use strict';

	document.addEventListener('DOMContentLoaded', () => {
		// Check if GSAP is available
		const hasGsap = typeof gsap !== 'undefined';

		// Get all accordion buttons
		const accordionButtons = document.querySelectorAll('.accordion button');

		accordionButtons.forEach(button => {
			// Handle accordion toggle
			button.addEventListener('click', function() {
				const accordion = this.closest('.accordion');
				const content = accordion.querySelector('.content');
				const inner = content.querySelector('.inner');
				const icon = this.querySelector('.icon');
				const isOpen = accordion.classList.contains('is-open');

				// CSS Fallback (Low Data Mode or GSAP not loaded)
				if (!hasGsap) {
					if (isOpen) {
						// Close immediately
						content.style.height = '0';
						accordion.classList.remove('is-open');
						this.setAttribute('aria-expanded', 'false');
						content.setAttribute('aria-hidden', 'true');
					} else {
						// Open immediately
						accordion.classList.add('is-open');
						content.style.height = 'auto';
						this.setAttribute('aria-expanded', 'true');
						content.setAttribute('aria-hidden', 'false');
					}
					return;
				}

				// GSAP Animation (Normal Mode)
				if (isOpen) {
					// Close accordion - animate content and icon simultaneously
					gsap.to(content, {
						height: 0,
						duration: 0.4,
						ease: 'power2.inOut',
						onComplete: function() {
							accordion.classList.remove('is-open');
							this.targets()[0].style.height = '';
						}
					});

					// Rotate icon back to 0 degrees
					gsap.to(icon, {
						rotation: 0,
						duration: 0.4,
						ease: 'power2.inOut'
					});

					// Update ARIA attributes
					this.setAttribute('aria-expanded', 'false');
					content.setAttribute('aria-hidden', 'true');
				} else {
					// Open accordion
					accordion.classList.add('is-open');

					// Get the natural height of the content
					const contentHeight = inner.offsetHeight;

					// Animate from 0 to natural height
					gsap.fromTo(content,
						{ height: 0 },
						{
							height: contentHeight,
							duration: 0.4,
							ease: 'power2.inOut',
							onComplete: function() {
								// Remove fixed height so content can be responsive
								this.targets()[0].style.height = 'auto';
							}
						}
					);

					// Rotate icon to 180 degrees
					gsap.to(icon, {
						rotation: 180,
						duration: 0.4,
						ease: 'power2.inOut'
					});

					// Update ARIA attributes
					this.setAttribute('aria-expanded', 'true');
					content.setAttribute('aria-hidden', 'false');

          ////////////////////////////////////////
					// Optional: Close other accordions (uncomment for accordion group behavior)
					// const siblings = Array.from(accordion.parentElement.children).filter(el =>
					//   el !== accordion && el.classList.contains('accordion') && el.classList.contains('is-open')
					// );
					// siblings.forEach(sibling => {
					//   const siblingContent = sibling.querySelector('.content');
					//   const siblingButton = sibling.querySelector('button');
					//   const siblingIcon = siblingButton.querySelector('.icon');
					
					//   gsap.to(siblingContent, {
					//     height: 0,
					//     duration: 0.4,
					//     ease: 'power2.inOut',
					//     onComplete: function() {
					//       sibling.classList.remove('is-open');
					//       this.targets()[0].style.height = '';
					//     }
					//   });
					
					//   gsap.to(siblingIcon, {
					//     rotation: 0,
					//     duration: 0.4,
					//     ease: 'power2.inOut'
					//   });
					
					//   siblingButton.setAttribute('aria-expanded', 'false');
					//   siblingContent.setAttribute('aria-hidden', 'true');
					// });
          ////////////////////////////////////////
          ////////////////////////////////////////
				}
			});

			// Keyboard accessibility
			button.addEventListener('keydown', function(e) {
				// Space or Enter key
				if (e.key === ' ' || e.key === 'Enter') {
					e.preventDefault();
					this.click();
				}
			});
		});
	});
})();
