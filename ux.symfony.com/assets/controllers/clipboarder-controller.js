import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['source']
    static values = {
        source: String,
        animationClass: String,
        animationDuration: {type: Number, default: 500}
    }

    copy ({ params: { value } }) {
        const text = value
            ?? (this.hasSourceValue ? this.sourceValue : null)
            ?? (this.hasSourceTarget ? this.sourceTarget.textContent : null)
        ;

        navigator.clipboard.writeText(text).then(() => this.copied())
    }

    startAnimation() {
        if (this.hasAnimationClassValue) {
            this.element.classList.add(this.animationClassValue);
        }
    }

    stopAnimation() {
        if (this.hasAnimationClassValue) {
            this.element.classList.remove(this.animationClassValue);
        }
    }

    copied() {
        clearTimeout(this.timeout);
        this.startAnimation();
        this.timeout = setTimeout(this.stopAnimation.bind(this), this.animationDurationValue);
    }
}
