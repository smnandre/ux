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

    static targets = ['block', 'arrow'];

    connect() {
        useHover(this, { element: this.element });
    }

    mouseEnter(event) {
        this.blockTarget.classList.remove('invisible', 'opacity-0');
        this.blockTarget.classList.add('visible');

        const arrowLen = this.arrowTarget.offsetWidth;
        this.cleanup = autoUpdate(this.element, this.blockTarget, () => {
            computePosition(this.element, this.blockTarget, {
                placement: this.placementValue,
                middleware: [
                    offset(this.offsetValue),
                    flip(),
                    shift({ padding: 5 }),
                    arrow({ element: this.arrowTarget }),
                ],
            }).then(({ x, y, middlewareData, placement }) => {
                Object.assign(this.blockTarget.style, {
                    left: `${x}px`,
                    top: `${y}px`,
                });

                if (middlewareData.arrow) {
                    const side = placement.split('-')[0];
                    const { x, y } = middlewareData.arrow;
                    const staticSide = {top: 'bottom', right: 'left', bottom: 'top', left: 'right'}[side];

                    Object.assign(this.arrowTarget.style, {
                        left: x != null ? `${x}px` : '',
                        top: y != null ? `${y}px` : '',
                        // Ensure the static side gets unset when
                        // flipping to other placements' axes.
                        right: '',
                        bottom: '',
                        [staticSide]: `${-arrowLen / 2}px`,
                        transform: 'rotate(45deg)',
                    });
                }
            });
        });
    }

    mouseLeave(event) {
        this.cleanup();
        this.blockTarget.classList.add('invisible', 'opacity-0');
        this.blockTarget.classList.remove('visible');
    }
}
