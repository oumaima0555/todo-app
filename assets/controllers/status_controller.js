import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        status: Boolean
    }

    static targets = ["icon", "badge", "row"]

    async toggle(event) {
        event.preventDefault();

        // Optimistic UI update
        this.statusValue = !this.statusValue;
        this.updateVisuals(this.statusValue);

        try {
            const response = await fetch(this.urlValue, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            // Ensure server state matches
            if (data.status !== this.statusValue) {
                this.statusValue = data.status;
                this.updateVisuals(this.statusValue);
            }

            // Optional: Animate or toast
        } catch (error) {
            console.error('Error toggling status:', error);
            // Revert on error
            this.statusValue = !this.statusValue;
            this.updateVisuals(this.statusValue);
            alert("Une erreur est survenue lors de la mise à jour.");
        }
    }

    updateVisuals(isDone) {
        const icon = this.iconTarget;
        const badge = this.badgeTarget;
        const row = this.element; // The controller is on the row/card

        // Update Icon
        if (isDone) {
            icon.innerHTML = '<i class="bi bi-check-lg"></i>';
            icon.style.backgroundColor = 'var(--success-color)';
            icon.style.color = 'white';
            icon.style.border = 'none';
        } else {
            icon.innerHTML = '';
            icon.style.backgroundColor = 'transparent';
            icon.style.color = '';
            icon.style.border = '2px solid var(--text-muted)';
        }

        // Update Badge
        if (isDone) {
            badge.textContent = 'Terminée';
            badge.className = 'task-status-badge status-done';
        } else {
            badge.textContent = 'En cours';
            badge.className = 'task-status-badge status-todo';
        }

        // Update Row Styles
        if (isDone) {
            row.classList.remove('overdue');
        } else {
            // We can't easily re-check overdue logic in JS without passing deadline data, 
            // but usually removing 'overdue' on completion is correct.
            // If we untick it, and it was overdue, we might miss re-adding it, 
            // but that's acceptable for a simple toggle.
        }
    }
}
