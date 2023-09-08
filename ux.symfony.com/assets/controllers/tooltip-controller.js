import { Controller } from '@hotwired/stimulus';
import { arrow, autoUpdate, computePosition, flip, offset, shift } from '@floating-ui/dom';
import { useHover } from 'stimulus-use';

export default class extends Controller {
    static values = {
        offset: {
            type: Number,
            default: 10,
        },
        placement: {
            type: String,
            default: 'bottom',
        },
    };

    static targets = ['block', 'arrow', 'aim'];

    connect() {
        useHover(this, { element: this.element });
    }

    mouseEnter(event) {
        this.blockTarget.classList.remove('hidden');

        this.cleanup = autoUpdate(this.aimTarget, this.blockTarget, () => {
            computePosition(this.aimTarget, this.blockTarget, {
                placement: this.placementValue,
                middleware: [
                    offset(this.offsetValue), flip(), shift({ padding: 5 }), arrow({ element: this.arrowTarget }),
                ],
            }).then(({ x, y, middlewareData }) => {
                Object.assign(this.blockTarget.style, {
                    left: `${x}px`,
                    top: `${y}px`,
                });

                if (middlewareData.arrow) {
                    const { x } = middlewareData.arrow;

                    Object.assign(this.arrowTarget.style, {
                        left: `${x}px`,
                        top: `${-this.arrowTarget.offsetHeight / 2}px`,
                    });
                }
            });
        });
    }

    mouseLeave(event) {
        this.cleanup();
        this.blockTarget.classList.add('hidden');
    }
}
