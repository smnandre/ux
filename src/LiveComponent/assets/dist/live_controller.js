import { Controller } from '@hotwired/stimulus';
import { Idiomorph } from 'idiomorph/dist/idiomorph.esm.js';

function parseDirectives(content) {
    const directives = [];
    if (!content) {
        return directives;
    }
    let currentActionName = '';
    let currentArgumentName = '';
    let currentArgumentValue = '';
    let currentArguments = [];
    let currentNamedArguments = {};
    let currentModifiers = [];
    let state = 'action';
    const getLastActionName = function () {
        if (currentActionName) {
            return currentActionName;
        }
        if (directives.length === 0) {
            throw new Error('Could not find any directives');
        }
        return directives[directives.length - 1].action;
    };
    const pushInstruction = function () {
        directives.push({
            action: currentActionName,
            args: currentArguments,
            named: currentNamedArguments,
            modifiers: currentModifiers,
            getString: () => {
                return content;
            }
        });
        currentActionName = '';
        currentArgumentName = '';
        currentArgumentValue = '';
        currentArguments = [];
        currentNamedArguments = {};
        currentModifiers = [];
        state = 'action';
    };
    const pushArgument = function () {
        const mixedArgTypesError = () => {
            throw new Error(`Normal and named arguments cannot be mixed inside "${currentActionName}()"`);
        };
        if (currentArgumentName) {
            if (currentArguments.length > 0) {
                mixedArgTypesError();
            }
            currentNamedArguments[currentArgumentName.trim()] = currentArgumentValue;
        }
        else {
            if (Object.keys(currentNamedArguments).length > 0) {
                mixedArgTypesError();
            }
            currentArguments.push(currentArgumentValue.trim());
        }
        currentArgumentName = '';
        currentArgumentValue = '';
    };
    const pushModifier = function () {
        if (currentArguments.length > 1) {
            throw new Error(`The modifier "${currentActionName}()" does not support multiple arguments.`);
        }
        if (Object.keys(currentNamedArguments).length > 0) {
            throw new Error(`The modifier "${currentActionName}()" does not support named arguments.`);
        }
        currentModifiers.push({
            name: currentActionName,
            value: currentArguments.length > 0 ? currentArguments[0] : null,
        });
        currentActionName = '';
        currentArgumentName = '';
        currentArguments = [];
        state = 'action';
    };
    for (let i = 0; i < content.length; i++) {
        const char = content[i];
        switch (state) {
            case 'action':
                if (char === '(') {
                    state = 'arguments';
                    break;
                }
                if (char === ' ') {
                    if (currentActionName) {
                        pushInstruction();
                    }
                    break;
                }
                if (char === '|') {
                    pushModifier();
                    break;
                }
                currentActionName += char;
                break;
            case 'arguments':
                if (char === ')') {
                    pushArgument();
                    state = 'after_arguments';
                    break;
                }
                if (char === ',') {
                    pushArgument();
                    break;
                }
                if (char === '=') {
                    currentArgumentName = currentArgumentValue;
                    currentArgumentValue = '';
                    break;
                }
                currentArgumentValue += char;
                break;
            case 'after_arguments':
                if (char === '|') {
                    pushModifier();
                    break;
                }
                if (char !== ' ') {
                    throw new Error(`Missing space after ${getLastActionName()}()`);
                }
                pushInstruction();
                break;
        }
    }
    switch (state) {
        case 'action':
        case 'after_arguments':
            if (currentActionName) {
                pushInstruction();
            }
            break;
        default:
            throw new Error(`Did you forget to add a closing ")" after "${currentActionName}"?`);
    }
    return directives;
}

function combineSpacedArray(parts) {
    const finalParts = [];
    parts.forEach((part) => {
        finalParts.push(...trimAll(part).split(' '));
    });
    return finalParts;
}
function trimAll(str) {
    return str.replace(/[\s]+/g, ' ').trim();
}
function normalizeModelName(model) {
    return (model
        .replace(/\[]$/, '')
        .split('[')
        .map(function (s) {
        return s.replace(']', '');
    })
        .join('.'));
}

function getValueFromElement(element, valueStore) {
    if (element instanceof HTMLInputElement) {
        if (element.type === 'checkbox') {
            const modelNameData = getModelDirectiveFromElement(element, false);
            if (modelNameData !== null) {
                const modelValue = valueStore.get(modelNameData.action);
                if (Array.isArray(modelValue)) {
                    return getMultipleCheckboxValue(element, modelValue);
                }
                else if (Object(modelValue) === modelValue) {
                    return getMultipleCheckboxValue(element, Object.values(modelValue));
                }
            }
            if (element.hasAttribute('value')) {
                return element.checked ? element.getAttribute('value') : null;
            }
            return element.checked;
        }
        return inputValue(element);
    }
    if (element instanceof HTMLSelectElement) {
        if (element.multiple) {
            return Array.from(element.selectedOptions).map((el) => el.value);
        }
        return element.value;
    }
    if (element.dataset.value) {
        return element.dataset.value;
    }
    if ('value' in element) {
        return element.value;
    }
    if (element.hasAttribute('value')) {
        return element.getAttribute('value');
    }
    return null;
}
function setValueOnElement(element, value) {
    if (element instanceof HTMLInputElement) {
        if (element.type === 'file') {
            return;
        }
        if (element.type === 'radio') {
            element.checked = element.value == value;
            return;
        }
        if (element.type === 'checkbox') {
            if (Array.isArray(value)) {
                let valueFound = false;
                value.forEach((val) => {
                    if (val == element.value) {
                        valueFound = true;
                    }
                });
                element.checked = valueFound;
            }
            else {
                if (element.hasAttribute('value')) {
                    element.checked = element.value == value;
                }
                else {
                    element.checked = value;
                }
            }
            return;
        }
    }
    if (element instanceof HTMLSelectElement) {
        const arrayWrappedValue = [].concat(value).map((value) => {
            return value + '';
        });
        Array.from(element.options).forEach((option) => {
            option.selected = arrayWrappedValue.includes(option.value);
        });
        return;
    }
    value = value === undefined ? '' : value;
    element.value = value;
}
function getAllModelDirectiveFromElements(element) {
    if (!element.dataset.model) {
        return [];
    }
    const directives = parseDirectives(element.dataset.model);
    directives.forEach((directive) => {
        if (directive.args.length > 0 || directive.named.length > 0) {
            throw new Error(`The data-model="${element.dataset.model}" format is invalid: it does not support passing arguments to the model.`);
        }
        directive.action = normalizeModelName(directive.action);
    });
    return directives;
}
function getModelDirectiveFromElement(element, throwOnMissing = true) {
    const dataModelDirectives = getAllModelDirectiveFromElements(element);
    if (dataModelDirectives.length > 0) {
        return dataModelDirectives[0];
    }
    if (element.getAttribute('name')) {
        const formElement = element.closest('form');
        if (formElement && 'model' in formElement.dataset) {
            const directives = parseDirectives(formElement.dataset.model || '*');
            const directive = directives[0];
            if (directive.args.length > 0 || directive.named.length > 0) {
                throw new Error(`The data-model="${formElement.dataset.model}" format is invalid: it does not support passing arguments to the model.`);
            }
            directive.action = normalizeModelName(element.getAttribute('name'));
            return directive;
        }
    }
    if (!throwOnMissing) {
        return null;
    }
    throw new Error(`Cannot determine the model name for "${getElementAsTagText(element)}": the element must either have a "data-model" (or "name" attribute living inside a <form data-model="*">).`);
}
function elementBelongsToThisComponent(element, component) {
    if (component.element === element) {
        return true;
    }
    if (!component.element.contains(element)) {
        return false;
    }
    let foundChildComponent = false;
    component.getChildren().forEach((childComponent) => {
        if (foundChildComponent) {
            return;
        }
        if (childComponent.element === element || childComponent.element.contains(element)) {
            foundChildComponent = true;
        }
    });
    return !foundChildComponent;
}
function cloneHTMLElement(element) {
    const newElement = element.cloneNode(true);
    if (!(newElement instanceof HTMLElement)) {
        throw new Error('Could not clone element');
    }
    return newElement;
}
function htmlToElement(html) {
    const template = document.createElement('template');
    html = html.trim();
    template.innerHTML = html;
    if (template.content.childElementCount > 1) {
        throw new Error(`Component HTML contains ${template.content.childElementCount} elements, but only 1 root element is allowed.`);
    }
    const child = template.content.firstElementChild;
    if (!child) {
        throw new Error('Child not found');
    }
    if (!(child instanceof HTMLElement)) {
        throw new Error(`Created element is not an HTMLElement: ${html.trim()}`);
    }
    return child;
}
function getElementAsTagText(element) {
    return element.innerHTML
        ? element.outerHTML.slice(0, element.outerHTML.indexOf(element.innerHTML))
        : element.outerHTML;
}
const getMultipleCheckboxValue = function (element, currentValues) {
    const finalValues = [...currentValues];
    const value = inputValue(element);
    const index = currentValues.indexOf(value);
    if (element.checked) {
        if (index === -1) {
            finalValues.push(value);
        }
        return finalValues;
    }
    if (index > -1) {
        finalValues.splice(index, 1);
    }
    return finalValues;
};
const inputValue = function (element) {
    return element.dataset.value ? element.dataset.value : element.value;
};

function getDeepData(data, propertyPath) {
    const { currentLevelData, finalKey } = parseDeepData(data, propertyPath);
    if (currentLevelData === undefined) {
        return undefined;
    }
    return currentLevelData[finalKey];
}
const parseDeepData = function (data, propertyPath) {
    const finalData = JSON.parse(JSON.stringify(data));
    let currentLevelData = finalData;
    const parts = propertyPath.split('.');
    for (let i = 0; i < parts.length - 1; i++) {
        currentLevelData = currentLevelData[parts[i]];
    }
    const finalKey = parts[parts.length - 1];
    return {
        currentLevelData,
        finalData,
        finalKey,
        parts,
    };
};

class ValueStore {
    constructor(props) {
        this.props = {};
        this.dirtyProps = {};
        this.pendingProps = {};
        this.updatedPropsFromParent = {};
        this.props = props;
    }
    get(name) {
        const normalizedName = normalizeModelName(name);
        if (this.dirtyProps[normalizedName] !== undefined) {
            return this.dirtyProps[normalizedName];
        }
        if (this.pendingProps[normalizedName] !== undefined) {
            return this.pendingProps[normalizedName];
        }
        if (this.props[normalizedName] !== undefined) {
            return this.props[normalizedName];
        }
        return getDeepData(this.props, normalizedName);
    }
    has(name) {
        return this.get(name) !== undefined;
    }
    set(name, value) {
        const normalizedName = normalizeModelName(name);
        if (this.get(normalizedName) === value) {
            return false;
        }
        this.dirtyProps[normalizedName] = value;
        return true;
    }
    getOriginalProps() {
        return Object.assign({}, this.props);
    }
    getDirtyProps() {
        return Object.assign({}, this.dirtyProps);
    }
    getUpdatedPropsFromParent() {
        return Object.assign({}, this.updatedPropsFromParent);
    }
    flushDirtyPropsToPending() {
        this.pendingProps = Object.assign({}, this.dirtyProps);
        this.dirtyProps = {};
    }
    reinitializeAllProps(props) {
        this.props = props;
        this.updatedPropsFromParent = {};
        this.pendingProps = {};
    }
    pushPendingPropsBackToDirty() {
        this.dirtyProps = Object.assign(Object.assign({}, this.pendingProps), this.dirtyProps);
        this.pendingProps = {};
    }
    storeNewPropsFromParent(props) {
        let changed = false;
        for (const [key, value] of Object.entries(props)) {
            const currentValue = this.get(key);
            if (currentValue !== value) {
                changed = true;
            }
        }
        if (changed) {
            this.updatedPropsFromParent = props;
        }
        return changed;
    }
}

function normalizeAttributesForComparison(element) {
    const isFileInput = element instanceof HTMLInputElement && element.type === 'file';
    if (!isFileInput) {
        if ('value' in element) {
            element.setAttribute('value', element.value);
        }
        else if (element.hasAttribute('value')) {
            element.setAttribute('value', '');
        }
    }
    Array.from(element.children).forEach((child) => {
        normalizeAttributesForComparison(child);
    });
}

function executeMorphdom(rootFromElement, rootToElement, modifiedFieldElements, getElementValue, childComponents, findChildComponent, getKeyFromElement, externalMutationTracker) {
    const childComponentMap = new Map();
    childComponents.forEach((childComponent) => {
        childComponentMap.set(childComponent.element, childComponent);
    });
    Idiomorph.morph(rootFromElement, rootToElement, {
        callbacks: {
            beforeNodeMorphed: (fromEl, toEl) => {
                if (!(fromEl instanceof Element) || !(toEl instanceof Element)) {
                    return true;
                }
                if (fromEl === rootFromElement) {
                    return true;
                }
                let idChanged = false;
                if (fromEl.hasAttribute('data-live-id')) {
                    if (fromEl.getAttribute('data-live-id') !== toEl.getAttribute('data-live-id')) {
                        for (const child of fromEl.children) {
                            child.setAttribute('parent-live-id-changed', '');
                        }
                        idChanged = true;
                    }
                }
                if (fromEl instanceof HTMLElement && toEl instanceof HTMLElement) {
                    if (typeof fromEl.__x !== 'undefined') {
                        if (!window.Alpine) {
                            throw new Error('Unable to access Alpine.js though the global window.Alpine variable. Please make sure Alpine.js is loaded before Symfony UX LiveComponent.');
                        }
                        if (typeof window.Alpine.morph !== 'function') {
                            throw new Error('Unable to access Alpine.js morph function. Please make sure the Alpine.js Morph plugin is installed and loaded, see https://alpinejs.dev/plugins/morph for more information.');
                        }
                        window.Alpine.morph(fromEl.__x, toEl);
                    }
                    if (childComponentMap.has(fromEl)) {
                        const childComponent = childComponentMap.get(fromEl);
                        return !childComponent.updateFromNewElementFromParentRender(toEl) && idChanged;
                    }
                    if (externalMutationTracker.wasElementAdded(fromEl)) {
                        fromEl.insertAdjacentElement('afterend', toEl);
                        return false;
                    }
                    if (modifiedFieldElements.includes(fromEl)) {
                        setValueOnElement(toEl, getElementValue(fromEl));
                    }
                    const elementChanges = externalMutationTracker.getChangedElement(fromEl);
                    if (elementChanges) {
                        elementChanges.applyToElement(toEl);
                    }
                    if (fromEl.nodeName.toUpperCase() !== 'OPTION' && fromEl.isEqualNode(toEl)) {
                        const normalizedFromEl = cloneHTMLElement(fromEl);
                        normalizeAttributesForComparison(normalizedFromEl);
                        const normalizedToEl = cloneHTMLElement(toEl);
                        normalizeAttributesForComparison(normalizedToEl);
                        if (normalizedFromEl.isEqualNode(normalizedToEl)) {
                            return false;
                        }
                    }
                }
                if (fromEl.hasAttribute('parent-live-id-changed')) {
                    fromEl.removeAttribute('parent-live-id-changed');
                    return true;
                }
                if (fromEl.hasAttribute('data-skip-morph')) {
                    fromEl.innerHTML = toEl.innerHTML;
                    return true;
                }
                if (fromEl.parentElement && fromEl.parentElement.hasAttribute('data-skip-morph')) {
                    return false;
                }
                return !fromEl.hasAttribute('data-live-ignore');
            },
            beforeNodeRemoved(node) {
                if (!(node instanceof HTMLElement)) {
                    return true;
                }
                if (externalMutationTracker.wasElementAdded(node)) {
                    return false;
                }
                return !node.hasAttribute('data-live-ignore');
            },
        },
    });
    childComponentMap.forEach((childComponent, element) => {
        var _a;
        const childComponentInResult = findChildComponent((_a = childComponent.id) !== null && _a !== void 0 ? _a : '', rootFromElement);
        if (null === childComponentInResult || element === childComponentInResult) {
            return;
        }
        childComponentInResult === null || childComponentInResult === void 0 ? void 0 : childComponentInResult.replaceWith(element);
        childComponent.updateFromNewElementFromParentRender(childComponentInResult);
    });
}

class UnsyncedInputsTracker {
    constructor(component, modelElementResolver) {
        this.elementEventListeners = [
            { event: 'input', callback: (event) => this.handleInputEvent(event) },
        ];
        this.component = component;
        this.modelElementResolver = modelElementResolver;
        this.unsyncedInputs = new UnsyncedInputContainer();
    }
    activate() {
        this.elementEventListeners.forEach(({ event, callback }) => {
            this.component.element.addEventListener(event, callback);
        });
    }
    deactivate() {
        this.elementEventListeners.forEach(({ event, callback }) => {
            this.component.element.removeEventListener(event, callback);
        });
    }
    markModelAsSynced(modelName) {
        this.unsyncedInputs.markModelAsSynced(modelName);
    }
    handleInputEvent(event) {
        const target = event.target;
        if (!target) {
            return;
        }
        this.updateModelFromElement(target);
    }
    updateModelFromElement(element) {
        if (!elementBelongsToThisComponent(element, this.component)) {
            return;
        }
        if (!(element instanceof HTMLElement)) {
            throw new Error('Could not update model for non HTMLElement');
        }
        const modelName = this.modelElementResolver.getModelName(element);
        this.unsyncedInputs.add(element, modelName);
    }
    getUnsyncedInputs() {
        return this.unsyncedInputs.allUnsyncedInputs();
    }
    getUnsyncedModels() {
        return Array.from(this.unsyncedInputs.getUnsyncedModelNames());
    }
    resetUnsyncedFields() {
        this.unsyncedInputs.resetUnsyncedFields();
    }
}
class UnsyncedInputContainer {
    constructor() {
        this.unsyncedNonModelFields = [];
        this.unsyncedModelNames = [];
        this.unsyncedModelFields = new Map();
    }
    add(element, modelName = null) {
        if (modelName) {
            this.unsyncedModelFields.set(modelName, element);
            if (!this.unsyncedModelNames.includes(modelName)) {
                this.unsyncedModelNames.push(modelName);
            }
            return;
        }
        this.unsyncedNonModelFields.push(element);
    }
    resetUnsyncedFields() {
        this.unsyncedModelFields.forEach((value, key) => {
            if (!this.unsyncedModelNames.includes(key)) {
                this.unsyncedModelFields.delete(key);
            }
        });
    }
    allUnsyncedInputs() {
        return [...this.unsyncedNonModelFields, ...this.unsyncedModelFields.values()];
    }
    markModelAsSynced(modelName) {
        const index = this.unsyncedModelNames.indexOf(modelName);
        if (index !== -1) {
            this.unsyncedModelNames.splice(index, 1);
        }
    }
    getUnsyncedModelNames() {
        return this.unsyncedModelNames;
    }
}

class HookManager {
    constructor() {
        this.hooks = new Map();
    }
    register(hookName, callback) {
        const hooks = this.hooks.get(hookName) || [];
        hooks.push(callback);
        this.hooks.set(hookName, hooks);
    }
    unregister(hookName, callback) {
        const hooks = this.hooks.get(hookName) || [];
        const index = hooks.indexOf(callback);
        if (index === -1) {
            return;
        }
        hooks.splice(index, 1);
        this.hooks.set(hookName, hooks);
    }
    triggerHook(hookName, ...args) {
        const hooks = this.hooks.get(hookName) || [];
        hooks.forEach((callback) => callback(...args));
    }
}

class BackendResponse {
    constructor(response) {
        this.response = response;
    }
    async getBody() {
        if (!this.body) {
            this.body = await this.response.text();
        }
        return this.body;
    }
}

class ChangingItemsTracker {
    constructor() {
        this.changedItems = new Map();
        this.removedItems = new Map();
    }
    setItem(itemName, newValue, previousValue) {
        if (this.removedItems.has(itemName)) {
            const removedRecord = this.removedItems.get(itemName);
            this.removedItems.delete(itemName);
            if (removedRecord.original === newValue) {
                return;
            }
        }
        if (this.changedItems.has(itemName)) {
            const originalRecord = this.changedItems.get(itemName);
            if (originalRecord.original === newValue) {
                this.changedItems.delete(itemName);
                return;
            }
            this.changedItems.set(itemName, { original: originalRecord.original, new: newValue });
            return;
        }
        this.changedItems.set(itemName, { original: previousValue, new: newValue });
    }
    removeItem(itemName, currentValue) {
        let trueOriginalValue = currentValue;
        if (this.changedItems.has(itemName)) {
            const originalRecord = this.changedItems.get(itemName);
            trueOriginalValue = originalRecord.original;
            this.changedItems.delete(itemName);
            if (trueOriginalValue === null) {
                return;
            }
        }
        if (!this.removedItems.has(itemName)) {
            this.removedItems.set(itemName, { original: trueOriginalValue });
        }
    }
    getChangedItems() {
        return Array.from(this.changedItems, ([name, { new: value }]) => ({ name, value }));
    }
    getRemovedItems() {
        return Array.from(this.removedItems.keys());
    }
    isEmpty() {
        return this.changedItems.size === 0 && this.removedItems.size === 0;
    }
}

class ElementChanges {
    constructor() {
        this.addedClasses = new Set();
        this.removedClasses = new Set();
        this.styleChanges = new ChangingItemsTracker();
        this.attributeChanges = new ChangingItemsTracker();
    }
    addClass(className) {
        if (!this.removedClasses.delete(className)) {
            this.addedClasses.add(className);
        }
    }
    removeClass(className) {
        if (!this.addedClasses.delete(className)) {
            this.removedClasses.add(className);
        }
    }
    addStyle(styleName, newValue, originalValue) {
        this.styleChanges.setItem(styleName, newValue, originalValue);
    }
    removeStyle(styleName, originalValue) {
        this.styleChanges.removeItem(styleName, originalValue);
    }
    addAttribute(attributeName, newValue, originalValue) {
        this.attributeChanges.setItem(attributeName, newValue, originalValue);
    }
    removeAttribute(attributeName, originalValue) {
        this.attributeChanges.removeItem(attributeName, originalValue);
    }
    getAddedClasses() {
        return [...this.addedClasses];
    }
    getRemovedClasses() {
        return [...this.removedClasses];
    }
    getChangedStyles() {
        return this.styleChanges.getChangedItems();
    }
    getRemovedStyles() {
        return this.styleChanges.getRemovedItems();
    }
    getChangedAttributes() {
        return this.attributeChanges.getChangedItems();
    }
    getRemovedAttributes() {
        return this.attributeChanges.getRemovedItems();
    }
    applyToElement(element) {
        element.classList.add(...this.addedClasses);
        element.classList.remove(...this.removedClasses);
        this.styleChanges.getChangedItems().forEach((change) => {
            element.style.setProperty(change.name, change.value);
            return;
        });
        this.styleChanges.getRemovedItems().forEach((styleName) => {
            element.style.removeProperty(styleName);
        });
        this.attributeChanges.getChangedItems().forEach((change) => {
            element.setAttribute(change.name, change.value);
        });
        this.attributeChanges.getRemovedItems().forEach((attributeName) => {
            element.removeAttribute(attributeName);
        });
    }
    isEmpty() {
        return (this.addedClasses.size === 0 &&
            this.removedClasses.size === 0 &&
            this.styleChanges.isEmpty() &&
            this.attributeChanges.isEmpty());
    }
}

class ExternalMutationTracker {
    constructor(element, shouldTrackChangeCallback) {
        this.changedElements = new WeakMap();
        this.changedElementsCount = 0;
        this.addedElements = [];
        this.removedElements = [];
        this.isStarted = false;
        this.element = element;
        this.shouldTrackChangeCallback = shouldTrackChangeCallback;
        this.mutationObserver = new MutationObserver(this.onMutations.bind(this));
    }
    start() {
        if (this.isStarted) {
            return;
        }
        this.mutationObserver.observe(this.element, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeOldValue: true,
        });
        this.isStarted = true;
    }
    stop() {
        if (this.isStarted) {
            this.mutationObserver.disconnect();
            this.isStarted = false;
        }
    }
    getChangedElement(element) {
        return this.changedElements.has(element) ? this.changedElements.get(element) : null;
    }
    getAddedElements() {
        return this.addedElements;
    }
    wasElementAdded(element) {
        return this.addedElements.includes(element);
    }
    handlePendingChanges() {
        this.onMutations(this.mutationObserver.takeRecords());
    }
    onMutations(mutations) {
        const handledAttributeMutations = new WeakMap();
        for (const mutation of mutations) {
            const element = mutation.target;
            if (!this.shouldTrackChangeCallback(element)) {
                continue;
            }
            if (this.isElementAddedByTranslation(element)) {
                continue;
            }
            let isChangeInAddedElement = false;
            for (const addedElement of this.addedElements) {
                if (addedElement.contains(element)) {
                    isChangeInAddedElement = true;
                    break;
                }
            }
            if (isChangeInAddedElement) {
                continue;
            }
            switch (mutation.type) {
                case 'childList':
                    this.handleChildListMutation(mutation);
                    break;
                case 'attributes':
                    if (!handledAttributeMutations.has(element)) {
                        handledAttributeMutations.set(element, []);
                    }
                    if (!handledAttributeMutations.get(element).includes(mutation.attributeName)) {
                        this.handleAttributeMutation(mutation);
                        handledAttributeMutations.set(element, [
                            ...handledAttributeMutations.get(element),
                            mutation.attributeName
                        ]);
                    }
                    break;
            }
        }
    }
    handleChildListMutation(mutation) {
        mutation.addedNodes.forEach((node) => {
            if (!(node instanceof Element)) {
                return;
            }
            if (this.removedElements.includes(node)) {
                this.removedElements.splice(this.removedElements.indexOf(node), 1);
                return;
            }
            if (this.isElementAddedByTranslation(node)) {
                return;
            }
            this.addedElements.push(node);
        });
        mutation.removedNodes.forEach((node) => {
            if (!(node instanceof Element)) {
                return;
            }
            if (this.addedElements.includes(node)) {
                this.addedElements.splice(this.addedElements.indexOf(node), 1);
                return;
            }
            this.removedElements.push(node);
        });
    }
    handleAttributeMutation(mutation) {
        const element = mutation.target;
        if (!this.changedElements.has(element)) {
            this.changedElements.set(element, new ElementChanges());
            this.changedElementsCount++;
        }
        const changedElement = this.changedElements.get(element);
        switch (mutation.attributeName) {
            case 'class':
                this.handleClassAttributeMutation(mutation, changedElement);
                break;
            case 'style':
                this.handleStyleAttributeMutation(mutation, changedElement);
                break;
            default:
                this.handleGenericAttributeMutation(mutation, changedElement);
        }
        if (changedElement.isEmpty()) {
            this.changedElements.delete(element);
            this.changedElementsCount--;
        }
    }
    handleClassAttributeMutation(mutation, elementChanges) {
        const element = mutation.target;
        const previousValue = mutation.oldValue;
        const previousValues = previousValue ? previousValue.split(' ') : [];
        previousValues.forEach((value, index) => {
            const trimmedValue = value.trim();
            if (trimmedValue !== '') {
                previousValues[index] = trimmedValue;
            }
            else {
                previousValues.splice(index, 1);
            }
        });
        const newValues = [].slice.call(element.classList);
        const addedValues = newValues.filter((value) => !previousValues.includes(value));
        const removedValues = previousValues.filter((value) => !newValues.includes(value));
        addedValues.forEach((value) => {
            elementChanges.addClass(value);
        });
        removedValues.forEach((value) => {
            elementChanges.removeClass(value);
        });
    }
    handleStyleAttributeMutation(mutation, elementChanges) {
        const element = mutation.target;
        const previousValue = mutation.oldValue || '';
        const previousStyles = this.extractStyles(previousValue);
        const newValue = element.getAttribute('style') || '';
        const newStyles = this.extractStyles(newValue);
        const addedOrChangedStyles = Object.keys(newStyles).filter((key) => previousStyles[key] === undefined || previousStyles[key] !== newStyles[key]);
        const removedStyles = Object.keys(previousStyles).filter((key) => !newStyles[key]);
        addedOrChangedStyles.forEach((style) => {
            elementChanges.addStyle(style, newStyles[style], previousStyles[style] === undefined ? null : previousStyles[style]);
        });
        removedStyles.forEach((style) => {
            elementChanges.removeStyle(style, previousStyles[style]);
        });
    }
    handleGenericAttributeMutation(mutation, elementChanges) {
        const attributeName = mutation.attributeName;
        const element = mutation.target;
        let oldValue = mutation.oldValue;
        let newValue = element.getAttribute(attributeName);
        if (oldValue === attributeName) {
            oldValue = '';
        }
        if (newValue === attributeName) {
            newValue = '';
        }
        if (!element.hasAttribute(attributeName)) {
            if (oldValue === null) {
                return;
            }
            elementChanges.removeAttribute(attributeName, mutation.oldValue);
            return;
        }
        if (newValue === oldValue) {
            return;
        }
        elementChanges.addAttribute(attributeName, element.getAttribute(attributeName), mutation.oldValue);
    }
    extractStyles(styles) {
        const styleObject = {};
        styles.split(';').forEach((style) => {
            const parts = style.split(':');
            if (parts.length === 1) {
                return;
            }
            const property = parts[0].trim();
            styleObject[property] = parts.slice(1).join(':').trim();
        });
        return styleObject;
    }
    isElementAddedByTranslation(element) {
        return element.tagName === 'FONT' && element.getAttribute('style') === 'vertical-align: inherit;';
    }
}

class ChildComponentWrapper {
    constructor(component, modelBindings) {
        this.component = component;
        this.modelBindings = modelBindings;
    }
}
class Component {
    constructor(element, name, props, listeners, componentFinder, fingerprint, id, backend, elementDriver) {
        this.defaultDebounce = 150;
        this.backendRequest = null;
        this.pendingActions = [];
        this.pendingFiles = {};
        this.isRequestPending = false;
        this.requestDebounceTimeout = null;
        this.children = new Map();
        this.parent = null;
        this.element = element;
        this.name = name;
        this.componentFinder = componentFinder;
        this.backend = backend;
        this.elementDriver = elementDriver;
        this.id = id;
        this.fingerprint = fingerprint;
        this.listeners = new Map();
        listeners.forEach((listener) => {
            var _a;
            if (!this.listeners.has(listener.event)) {
                this.listeners.set(listener.event, []);
            }
            (_a = this.listeners.get(listener.event)) === null || _a === void 0 ? void 0 : _a.push(listener.action);
        });
        this.valueStore = new ValueStore(props);
        this.unsyncedInputsTracker = new UnsyncedInputsTracker(this, elementDriver);
        this.hooks = new HookManager();
        this.resetPromise();
        this.externalMutationTracker = new ExternalMutationTracker(this.element, (element) => elementBelongsToThisComponent(element, this));
        this.externalMutationTracker.start();
        this.onChildComponentModelUpdate = this.onChildComponentModelUpdate.bind(this);
    }
    _swapBackend(backend) {
        this.backend = backend;
    }
    addPlugin(plugin) {
        plugin.attachToComponent(this);
    }
    connect() {
        this.hooks.triggerHook('connect', this);
        this.unsyncedInputsTracker.activate();
        this.externalMutationTracker.start();
    }
    disconnect() {
        this.hooks.triggerHook('disconnect', this);
        this.clearRequestDebounceTimeout();
        this.unsyncedInputsTracker.deactivate();
        this.externalMutationTracker.stop();
    }
    on(hookName, callback) {
        this.hooks.register(hookName, callback);
    }
    off(hookName, callback) {
        this.hooks.unregister(hookName, callback);
    }
    set(model, value, reRender = false, debounce = false) {
        const promise = this.nextRequestPromise;
        const modelName = normalizeModelName(model);
        if (!this.valueStore.has(modelName)) {
            throw new Error(`Invalid model name "${model}".`);
        }
        const isChanged = this.valueStore.set(modelName, value);
        this.hooks.triggerHook('model:set', model, value, this);
        this.unsyncedInputsTracker.markModelAsSynced(modelName);
        if (reRender && isChanged) {
            this.debouncedStartRequest(debounce);
        }
        return promise;
    }
    getData(model) {
        const modelName = normalizeModelName(model);
        if (!this.valueStore.has(modelName)) {
            throw new Error(`Invalid model "${model}".`);
        }
        return this.valueStore.get(modelName);
    }
    action(name, args = {}, debounce = false) {
        const promise = this.nextRequestPromise;
        this.pendingActions.push({
            name,
            args
        });
        this.debouncedStartRequest(debounce);
        return promise;
    }
    files(key, input) {
        this.pendingFiles[key] = input;
    }
    render() {
        const promise = this.nextRequestPromise;
        this.tryStartingRequest();
        return promise;
    }
    getUnsyncedModels() {
        return this.unsyncedInputsTracker.getUnsyncedModels();
    }
    addChild(child, modelBindings = []) {
        if (!child.id) {
            throw new Error('Children components must have an id.');
        }
        this.children.set(child.id, new ChildComponentWrapper(child, modelBindings));
        child.parent = this;
        child.on('model:set', this.onChildComponentModelUpdate);
    }
    removeChild(child) {
        if (!child.id) {
            throw new Error('Children components must have an id.');
        }
        this.children.delete(child.id);
        child.parent = null;
        child.off('model:set', this.onChildComponentModelUpdate);
    }
    getParent() {
        return this.parent;
    }
    getChildren() {
        const children = new Map();
        this.children.forEach((childComponent, id) => {
            children.set(id, childComponent.component);
        });
        return children;
    }
    emit(name, data, onlyMatchingComponentsNamed = null) {
        return this.performEmit(name, data, false, onlyMatchingComponentsNamed);
    }
    emitUp(name, data, onlyMatchingComponentsNamed = null) {
        return this.performEmit(name, data, true, onlyMatchingComponentsNamed);
    }
    emitSelf(name, data) {
        return this.doEmit(name, data);
    }
    performEmit(name, data, emitUp, matchingName) {
        const components = this.componentFinder(this, emitUp, matchingName);
        components.forEach((component) => {
            component.doEmit(name, data);
        });
    }
    doEmit(name, data) {
        if (!this.listeners.has(name)) {
            return;
        }
        const actions = this.listeners.get(name) || [];
        actions.forEach((action) => {
            this.action(action, data, 1);
        });
    }
    updateFromNewElementFromParentRender(toEl) {
        const props = this.elementDriver.getComponentProps(toEl);
        if (props === null) {
            return false;
        }
        const isChanged = this.valueStore.storeNewPropsFromParent(props);
        const fingerprint = toEl.dataset.liveFingerprintValue;
        if (fingerprint !== undefined) {
            this.fingerprint = fingerprint;
        }
        if (isChanged) {
            this.render();
        }
        return isChanged;
    }
    onChildComponentModelUpdate(modelName, value, childComponent) {
        if (!childComponent.id) {
            throw new Error('Missing id');
        }
        const childWrapper = this.children.get(childComponent.id);
        if (!childWrapper) {
            throw new Error('Missing child');
        }
        childWrapper.modelBindings.forEach((modelBinding) => {
            const childModelName = modelBinding.innerModelName || 'value';
            if (childModelName !== modelName) {
                return;
            }
            this.set(modelBinding.modelName, value, modelBinding.shouldRender, modelBinding.debounce);
        });
    }
    isTurboEnabled() {
        return typeof Turbo !== 'undefined' && !this.element.closest('[data-turbo="false"]');
    }
    tryStartingRequest() {
        if (!this.backendRequest) {
            this.performRequest();
            return;
        }
        this.isRequestPending = true;
    }
    performRequest() {
        const thisPromiseResolve = this.nextRequestPromiseResolve;
        this.resetPromise();
        this.unsyncedInputsTracker.resetUnsyncedFields();
        const filesToSend = {};
        for (const [key, value] of Object.entries(this.pendingFiles)) {
            if (value.files) {
                filesToSend[key] = value.files;
            }
        }
        this.backendRequest = this.backend.makeRequest(this.valueStore.getOriginalProps(), this.pendingActions, this.valueStore.getDirtyProps(), this.getChildrenFingerprints(), this.valueStore.getUpdatedPropsFromParent(), filesToSend);
        this.hooks.triggerHook('loading.state:started', this.element, this.backendRequest);
        this.pendingActions = [];
        this.valueStore.flushDirtyPropsToPending();
        this.isRequestPending = false;
        this.backendRequest.promise.then(async (response) => {
            const backendResponse = new BackendResponse(response);
            const html = await backendResponse.getBody();
            for (const input of Object.values(this.pendingFiles)) {
                input.value = '';
            }
            const headers = backendResponse.response.headers;
            if (headers.get('Content-Type') !== 'application/vnd.live-component+html' && !headers.get('X-Live-Redirect')) {
                const controls = { displayError: true };
                this.valueStore.pushPendingPropsBackToDirty();
                this.hooks.triggerHook('response:error', backendResponse, controls);
                if (controls.displayError) {
                    this.renderError(html);
                }
                this.backendRequest = null;
                thisPromiseResolve(backendResponse);
                return response;
            }
            this.processRerender(html, backendResponse);
            this.backendRequest = null;
            thisPromiseResolve(backendResponse);
            if (this.isRequestPending) {
                this.isRequestPending = false;
                this.performRequest();
            }
            return response;
        });
    }
    processRerender(html, backendResponse) {
        const controls = { shouldRender: true };
        this.hooks.triggerHook('render:started', html, backendResponse, controls);
        if (!controls.shouldRender) {
            return;
        }
        if (backendResponse.response.headers.get('Location')) {
            if (this.isTurboEnabled()) {
                Turbo.visit(backendResponse.response.headers.get('Location'));
            }
            else {
                window.location.href = backendResponse.response.headers.get('Location') || '';
            }
            return;
        }
        this.hooks.triggerHook('loading.state:finished', this.element);
        const modifiedModelValues = {};
        Object.keys(this.valueStore.getDirtyProps()).forEach((modelName) => {
            modifiedModelValues[modelName] = this.valueStore.get(modelName);
        });
        let newElement;
        try {
            newElement = htmlToElement(html);
            if (!newElement.matches('[data-controller~=live]')) {
                throw new Error('A live component template must contain a single root controller element.');
            }
        }
        catch (error) {
            console.error('There was a problem with the component HTML returned:');
            throw error;
        }
        const newProps = this.elementDriver.getComponentProps(newElement);
        this.valueStore.reinitializeAllProps(newProps);
        const eventsToEmit = this.elementDriver.getEventsToEmit(newElement);
        const browserEventsToDispatch = this.elementDriver.getBrowserEventsToDispatch(newElement);
        this.externalMutationTracker.handlePendingChanges();
        this.externalMutationTracker.stop();
        executeMorphdom(this.element, newElement, this.unsyncedInputsTracker.getUnsyncedInputs(), (element) => getValueFromElement(element, this.valueStore), Array.from(this.getChildren().values()), this.elementDriver.findChildComponentElement, this.elementDriver.getKeyFromElement, this.externalMutationTracker);
        this.externalMutationTracker.start();
        Object.keys(modifiedModelValues).forEach((modelName) => {
            this.valueStore.set(modelName, modifiedModelValues[modelName]);
        });
        eventsToEmit.forEach(({ event, data, target, componentName }) => {
            if (target === 'up') {
                this.emitUp(event, data, componentName);
                return;
            }
            if (target === 'self') {
                this.emitSelf(event, data);
                return;
            }
            this.emit(event, data, componentName);
        });
        browserEventsToDispatch.forEach(({ event, payload }) => {
            this.element.dispatchEvent(new CustomEvent(event, {
                detail: payload,
                bubbles: true,
            }));
        });
        this.hooks.triggerHook('render:finished', this);
    }
    calculateDebounce(debounce) {
        if (debounce === true) {
            return this.defaultDebounce;
        }
        if (debounce === false) {
            return 0;
        }
        return debounce;
    }
    clearRequestDebounceTimeout() {
        if (this.requestDebounceTimeout) {
            clearTimeout(this.requestDebounceTimeout);
            this.requestDebounceTimeout = null;
        }
    }
    debouncedStartRequest(debounce) {
        this.clearRequestDebounceTimeout();
        this.requestDebounceTimeout = window.setTimeout(() => {
            this.render();
        }, this.calculateDebounce(debounce));
    }
    renderError(html) {
        let modal = document.getElementById('live-component-error');
        if (modal) {
            modal.innerHTML = '';
        }
        else {
            modal = document.createElement('div');
            modal.id = 'live-component-error';
            modal.style.padding = '50px';
            modal.style.backgroundColor = 'rgba(0, 0, 0, .5)';
            modal.style.zIndex = '100000';
            modal.style.position = 'fixed';
            modal.style.top = '0px';
            modal.style.bottom = '0px';
            modal.style.left = '0px';
            modal.style.right = '0px';
            modal.style.display = 'flex';
            modal.style.flexDirection = 'column';
        }
        const iframe = document.createElement('iframe');
        iframe.style.borderRadius = '5px';
        iframe.style.flexGrow = '1';
        modal.appendChild(iframe);
        document.body.prepend(modal);
        document.body.style.overflow = 'hidden';
        if (iframe.contentWindow) {
            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(html);
            iframe.contentWindow.document.close();
        }
        const closeModal = (modal) => {
            if (modal) {
                modal.outerHTML = '';
            }
            document.body.style.overflow = 'visible';
        };
        modal.addEventListener('click', () => closeModal(modal));
        modal.setAttribute('tabindex', '0');
        modal.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeModal(modal);
            }
        });
        modal.focus();
    }
    getChildrenFingerprints() {
        const fingerprints = {};
        this.children.forEach((childComponent) => {
            const child = childComponent.component;
            if (!child.id) {
                throw new Error('missing id');
            }
            fingerprints[child.id] = {
                fingerprint: child.fingerprint,
                tag: child.element.tagName.toLowerCase(),
            };
        });
        return fingerprints;
    }
    resetPromise() {
        this.nextRequestPromise = new Promise((resolve) => {
            this.nextRequestPromiseResolve = resolve;
        });
    }
}
function proxifyComponent(component) {
    return new Proxy(component, {
        get(component, prop) {
            if (prop in component || typeof prop !== 'string') {
                if (typeof component[prop] === 'function') {
                    const callable = component[prop];
                    return (...args) => {
                        return callable.apply(component, args);
                    };
                }
                return Reflect.get(component, prop);
            }
            if (component.valueStore.has(prop)) {
                return component.getData(prop);
            }
            return (args) => {
                return component.action.apply(component, [prop, args]);
            };
        },
        set(target, property, value) {
            if (property in target) {
                target[property] = value;
                return true;
            }
            target.set(property, value);
            return true;
        },
    });
}

class BackendRequest {
    constructor(promise, actions, updateModels) {
        this.isResolved = false;
        this.promise = promise;
        this.promise.then((response) => {
            this.isResolved = true;
            return response;
        });
        this.actions = actions;
        this.updatedModels = updateModels;
    }
    containsOneOfActions(targetedActions) {
        return this.actions.filter((action) => targetedActions.includes(action)).length > 0;
    }
    areAnyModelsUpdated(targetedModels) {
        return this.updatedModels.filter((model) => targetedModels.includes(model)).length > 0;
    }
}

class RequestBuilder {
    constructor(url, method = 'post', csrfToken = null) {
        this.url = url;
        this.method = method;
        this.csrfToken = csrfToken;
    }
    buildRequest(props, actions, updated, children, updatedPropsFromParent, files) {
        const splitUrl = this.url.split('?');
        let [url] = splitUrl;
        const [, queryString] = splitUrl;
        const params = new URLSearchParams(queryString || '');
        const fetchOptions = {};
        fetchOptions.headers = {
            Accept: 'application/vnd.live-component+html',
            'X-Requested-With': 'XMLHttpRequest',
        };
        const totalFiles = Object.entries(files).reduce((total, current) => total + current.length, 0);
        const hasFingerprints = Object.keys(children).length > 0;
        if (actions.length === 0 &&
            totalFiles === 0 &&
            this.method === 'get' &&
            this.willDataFitInUrl(JSON.stringify(props), JSON.stringify(updated), params, JSON.stringify(children), JSON.stringify(updatedPropsFromParent))) {
            params.set('props', JSON.stringify(props));
            params.set('updated', JSON.stringify(updated));
            if (Object.keys(updatedPropsFromParent).length > 0) {
                params.set('propsFromParent', JSON.stringify(updatedPropsFromParent));
            }
            if (hasFingerprints) {
                params.set('children', JSON.stringify(children));
            }
            fetchOptions.method = 'GET';
        }
        else {
            fetchOptions.method = 'POST';
            const requestData = { props, updated };
            if (Object.keys(updatedPropsFromParent).length > 0) {
                requestData.propsFromParent = updatedPropsFromParent;
            }
            if (hasFingerprints) {
                requestData.children = children;
            }
            if (this.csrfToken &&
                (actions.length || totalFiles)) {
                fetchOptions.headers['X-CSRF-TOKEN'] = this.csrfToken;
            }
            if (actions.length > 0) {
                if (actions.length === 1) {
                    requestData.args = actions[0].args;
                    url += `/${encodeURIComponent(actions[0].name)}`;
                }
                else {
                    url += '/_batch';
                    requestData.actions = actions;
                }
            }
            const formData = new FormData();
            formData.append('data', JSON.stringify(requestData));
            for (const [key, value] of Object.entries(files)) {
                const length = value.length;
                for (let i = 0; i < length; ++i) {
                    formData.append(key, value[i]);
                }
            }
            fetchOptions.body = formData;
        }
        const paramsString = params.toString();
        return {
            url: `${url}${paramsString.length > 0 ? `?${paramsString}` : ''}`,
            fetchOptions,
        };
    }
    willDataFitInUrl(propsJson, updatedJson, params, childrenJson, propsFromParentJson) {
        const urlEncodedJsonData = new URLSearchParams(propsJson + updatedJson + childrenJson + propsFromParentJson).toString();
        return (urlEncodedJsonData + params.toString()).length < 1500;
    }
}

class Backend {
    constructor(url, method = 'post', csrfToken = null) {
        this.requestBuilder = new RequestBuilder(url, method, csrfToken);
    }
    makeRequest(props, actions, updated, children, updatedPropsFromParent, files) {
        const { url, fetchOptions } = this.requestBuilder.buildRequest(props, actions, updated, children, updatedPropsFromParent, files);
        return new BackendRequest(fetch(url, fetchOptions), actions.map((backendAction) => backendAction.name), Object.keys(updated));
    }
}

class StandardElementDriver {
    getModelName(element) {
        const modelDirective = getModelDirectiveFromElement(element, false);
        if (!modelDirective) {
            return null;
        }
        return modelDirective.action;
    }
    getComponentProps(rootElement) {
        var _a;
        const propsJson = (_a = rootElement.dataset.livePropsValue) !== null && _a !== void 0 ? _a : '{}';
        return JSON.parse(propsJson);
    }
    findChildComponentElement(id, element) {
        return element.querySelector(`[data-live-id=${id}]`);
    }
    getKeyFromElement(element) {
        return element.dataset.liveId || null;
    }
    getEventsToEmit(element) {
        var _a;
        const eventsJson = (_a = element.dataset.liveEmit) !== null && _a !== void 0 ? _a : '[]';
        return JSON.parse(eventsJson);
    }
    getBrowserEventsToDispatch(element) {
        var _a;
        const eventsJson = (_a = element.dataset.liveBrowserDispatch) !== null && _a !== void 0 ? _a : '[]';
        return JSON.parse(eventsJson);
    }
}

class LoadingPlugin {
    attachToComponent(component) {
        component.on('loading.state:started', (element, request) => {
            this.startLoading(component, element, request);
        });
        component.on('loading.state:finished', (element) => {
            this.finishLoading(component, element);
        });
        this.finishLoading(component, component.element);
    }
    startLoading(component, targetElement, backendRequest) {
        this.handleLoadingToggle(component, true, targetElement, backendRequest);
    }
    finishLoading(component, targetElement) {
        this.handleLoadingToggle(component, false, targetElement, null);
    }
    handleLoadingToggle(component, isLoading, targetElement, backendRequest) {
        if (isLoading) {
            this.addAttributes(targetElement, ['busy']);
        }
        else {
            this.removeAttributes(targetElement, ['busy']);
        }
        this.getLoadingDirectives(component, targetElement).forEach(({ element, directives }) => {
            if (isLoading) {
                this.addAttributes(element, ['data-live-is-loading']);
            }
            else {
                this.removeAttributes(element, ['data-live-is-loading']);
            }
            directives.forEach((directive) => {
                this.handleLoadingDirective(element, isLoading, directive, backendRequest);
            });
        });
    }
    handleLoadingDirective(element, isLoading, directive, backendRequest) {
        const finalAction = parseLoadingAction(directive.action, isLoading);
        const targetedActions = [];
        const targetedModels = [];
        let delay = 0;
        const validModifiers = new Map();
        validModifiers.set('delay', (modifier) => {
            if (!isLoading) {
                return;
            }
            delay = modifier.value ? parseInt(modifier.value) : 200;
        });
        validModifiers.set('action', (modifier) => {
            if (!modifier.value) {
                throw new Error(`The "action" in data-loading must have an action name - e.g. action(foo). It's missing for "${directive.getString()}"`);
            }
            targetedActions.push(modifier.value);
        });
        validModifiers.set('model', (modifier) => {
            if (!modifier.value) {
                throw new Error(`The "model" in data-loading must have an action name - e.g. model(foo). It's missing for "${directive.getString()}"`);
            }
            targetedModels.push(modifier.value);
        });
        directive.modifiers.forEach((modifier) => {
            var _a;
            if (validModifiers.has(modifier.name)) {
                const callable = (_a = validModifiers.get(modifier.name)) !== null && _a !== void 0 ? _a : (() => { });
                callable(modifier);
                return;
            }
            throw new Error(`Unknown modifier "${modifier.name}" used in data-loading="${directive.getString()}". Available modifiers are: ${Array.from(validModifiers.keys()).join(', ')}.`);
        });
        if (isLoading && targetedActions.length > 0 && backendRequest && !backendRequest.containsOneOfActions(targetedActions)) {
            return;
        }
        if (isLoading && targetedModels.length > 0 && backendRequest && !backendRequest.areAnyModelsUpdated(targetedModels)) {
            return;
        }
        let loadingDirective;
        switch (finalAction) {
            case 'show':
                loadingDirective = () => this.showElement(element);
                break;
            case 'hide':
                loadingDirective = () => this.hideElement(element);
                break;
            case 'addClass':
                loadingDirective = () => this.addClass(element, directive.args);
                break;
            case 'removeClass':
                loadingDirective = () => this.removeClass(element, directive.args);
                break;
            case 'addAttribute':
                loadingDirective = () => this.addAttributes(element, directive.args);
                break;
            case 'removeAttribute':
                loadingDirective = () => this.removeAttributes(element, directive.args);
                break;
            default:
                throw new Error(`Unknown data-loading action "${finalAction}"`);
        }
        if (delay) {
            window.setTimeout(() => {
                if (backendRequest && !backendRequest.isResolved) {
                    loadingDirective();
                }
            }, delay);
            return;
        }
        loadingDirective();
    }
    getLoadingDirectives(component, element) {
        const loadingDirectives = [];
        let matchingElements = [...element.querySelectorAll('[data-loading]')];
        matchingElements = matchingElements.filter((elt) => elementBelongsToThisComponent(elt, component));
        if (element.hasAttribute('data-loading')) {
            matchingElements = [element, ...matchingElements];
        }
        matchingElements.forEach((element => {
            if (!(element instanceof HTMLElement) && !(element instanceof SVGElement)) {
                throw new Error('Invalid Element Type');
            }
            const directives = parseDirectives(element.dataset.loading || 'show');
            loadingDirectives.push({
                element,
                directives,
            });
        }));
        return loadingDirectives;
    }
    showElement(element) {
        element.style.display = 'revert';
    }
    hideElement(element) {
        element.style.display = 'none';
    }
    addClass(element, classes) {
        element.classList.add(...combineSpacedArray(classes));
    }
    removeClass(element, classes) {
        element.classList.remove(...combineSpacedArray(classes));
        if (element.classList.length === 0) {
            element.removeAttribute('class');
        }
    }
    addAttributes(element, attributes) {
        attributes.forEach((attribute) => {
            element.setAttribute(attribute, '');
        });
    }
    removeAttributes(element, attributes) {
        attributes.forEach((attribute) => {
            element.removeAttribute(attribute);
        });
    }
}
const parseLoadingAction = function (action, isLoading) {
    switch (action) {
        case 'show':
            return isLoading ? 'show' : 'hide';
        case 'hide':
            return isLoading ? 'hide' : 'show';
        case 'addClass':
            return isLoading ? 'addClass' : 'removeClass';
        case 'removeClass':
            return isLoading ? 'removeClass' : 'addClass';
        case 'addAttribute':
            return isLoading ? 'addAttribute' : 'removeAttribute';
        case 'removeAttribute':
            return isLoading ? 'removeAttribute' : 'addAttribute';
    }
    throw new Error(`Unknown data-loading action "${action}"`);
};

class ValidatedFieldsPlugin {
    attachToComponent(component) {
        component.on('model:set', (modelName) => {
            this.handleModelSet(modelName, component.valueStore);
        });
    }
    handleModelSet(modelName, valueStore) {
        if (valueStore.has('validatedFields')) {
            const validatedFields = [...valueStore.get('validatedFields')];
            if (!validatedFields.includes(modelName)) {
                validatedFields.push(modelName);
            }
            valueStore.set('validatedFields', validatedFields);
        }
    }
}

class PageUnloadingPlugin {
    constructor() {
        this.isConnected = false;
    }
    attachToComponent(component) {
        component.on('render:started', (html, response, controls) => {
            if (!this.isConnected) {
                controls.shouldRender = false;
            }
        });
        component.on('connect', () => {
            this.isConnected = true;
        });
        component.on('disconnect', () => {
            this.isConnected = false;
        });
    }
}

class PollingDirector {
    constructor(component) {
        this.isPollingActive = true;
        this.pollingIntervals = [];
        this.component = component;
    }
    addPoll(actionName, duration) {
        this.polls.push({ actionName, duration });
        if (this.isPollingActive) {
            this.initiatePoll(actionName, duration);
        }
    }
    startAllPolling() {
        if (this.isPollingActive) {
            return;
        }
        this.isPollingActive = true;
        this.polls.forEach(({ actionName, duration }) => {
            this.initiatePoll(actionName, duration);
        });
    }
    stopAllPolling() {
        this.isPollingActive = false;
        this.pollingIntervals.forEach((interval) => {
            clearInterval(interval);
        });
    }
    clearPolling() {
        this.stopAllPolling();
        this.polls = [];
        this.startAllPolling();
    }
    initiatePoll(actionName, duration) {
        let callback;
        if (actionName === '$render') {
            callback = () => {
                this.component.render();
            };
        }
        else {
            callback = () => {
                this.component.action(actionName, {}, 0);
            };
        }
        const timer = setInterval(() => {
            callback();
        }, duration);
        this.pollingIntervals.push(timer);
    }
}

class PollingPlugin {
    attachToComponent(component) {
        this.element = component.element;
        this.pollingDirector = new PollingDirector(component);
        this.initializePolling();
        component.on('connect', () => {
            this.pollingDirector.startAllPolling();
        });
        component.on('disconnect', () => {
            this.pollingDirector.stopAllPolling();
        });
        component.on('render:finished', () => {
            this.initializePolling();
        });
    }
    addPoll(actionName, duration) {
        this.pollingDirector.addPoll(actionName, duration);
    }
    clearPolling() {
        this.pollingDirector.clearPolling();
    }
    initializePolling() {
        this.clearPolling();
        if (this.element.dataset.poll === undefined) {
            return;
        }
        const rawPollConfig = this.element.dataset.poll;
        const directives = parseDirectives(rawPollConfig || '$render');
        directives.forEach((directive) => {
            let duration = 2000;
            directive.modifiers.forEach((modifier) => {
                switch (modifier.name) {
                    case 'delay':
                        if (modifier.value) {
                            duration = parseInt(modifier.value);
                        }
                        break;
                    default:
                        console.warn(`Unknown modifier "${modifier.name}" in data-poll "${rawPollConfig}".`);
                }
            });
            this.addPoll(directive.action, duration);
        });
    }
}

class SetValueOntoModelFieldsPlugin {
    attachToComponent(component) {
        this.synchronizeValueOfModelFields(component);
        component.on('render:finished', () => {
            this.synchronizeValueOfModelFields(component);
        });
    }
    synchronizeValueOfModelFields(component) {
        component.element.querySelectorAll('[data-model]').forEach((element) => {
            if (!(element instanceof HTMLElement)) {
                throw new Error('Invalid element using data-model.');
            }
            if (element instanceof HTMLFormElement) {
                return;
            }
            if (!elementBelongsToThisComponent(element, component)) {
                return;
            }
            const modelDirective = getModelDirectiveFromElement(element);
            if (!modelDirective) {
                return;
            }
            const modelName = modelDirective.action;
            if (component.getUnsyncedModels().includes(modelName)) {
                return;
            }
            if (component.valueStore.has(modelName)) {
                setValueOnElement(element, component.valueStore.get(modelName));
            }
            if (element instanceof HTMLSelectElement && !element.multiple) {
                component.valueStore.set(modelName, getValueFromElement(element, component.valueStore));
            }
        });
    }
}

function getModelBinding (modelDirective) {
    let shouldRender = true;
    let targetEventName = null;
    let debounce = false;
    modelDirective.modifiers.forEach((modifier) => {
        switch (modifier.name) {
            case 'on':
                if (!modifier.value) {
                    throw new Error(`The "on" modifier in ${modelDirective.getString()} requires a value - e.g. on(change).`);
                }
                if (!['input', 'change'].includes(modifier.value)) {
                    throw new Error(`The "on" modifier in ${modelDirective.getString()} only accepts the arguments "input" or "change".`);
                }
                targetEventName = modifier.value;
                break;
            case 'norender':
                shouldRender = false;
                break;
            case 'debounce':
                debounce = modifier.value ? parseInt(modifier.value) : true;
                break;
            default:
                throw new Error(`Unknown modifier "${modifier.name}" in data-model="${modelDirective.getString()}".`);
        }
    });
    const [modelName, innerModelName] = modelDirective.action.split(':');
    return {
        modelName,
        innerModelName: innerModelName || null,
        shouldRender,
        debounce,
        targetEventName
    };
}

class ComponentRegistry {
    constructor() {
        this.componentMapByElement = new WeakMap();
        this.componentMapByComponent = new Map();
    }
    registerComponent(element, component) {
        this.componentMapByElement.set(element, component);
        this.componentMapByComponent.set(component, component.name);
    }
    unregisterComponent(component) {
        this.componentMapByElement.delete(component.element);
        this.componentMapByComponent.delete(component);
    }
    getComponent(element) {
        return new Promise((resolve, reject) => {
            let count = 0;
            const maxCount = 10;
            const interval = setInterval(() => {
                const component = this.componentMapByElement.get(element);
                if (component) {
                    clearInterval(interval);
                    resolve(component);
                }
                count++;
                if (count > maxCount) {
                    clearInterval(interval);
                    reject(new Error(`Component not found for element ${getElementAsTagText(element)}`));
                }
            }, 5);
        });
    }
    findComponents(currentComponent, onlyParents, onlyMatchName) {
        const components = [];
        this.componentMapByComponent.forEach((componentName, component) => {
            if (onlyParents &&
                (currentComponent === component || !component.element.contains(currentComponent.element))) {
                return;
            }
            if (onlyMatchName && componentName !== onlyMatchName) {
                return;
            }
            components.push(component);
        });
        return components;
    }
}

function isValueEmpty(value) {
    if (null === value || value === '' || undefined === value || (Array.isArray(value) && value.length === 0)) {
        return true;
    }
    if (typeof value !== 'object') {
        return false;
    }
    for (const key of Object.keys(value)) {
        if (!isValueEmpty(value[key])) {
            return false;
        }
    }
    return true;
}
function toQueryString(data) {
    const buildQueryStringEntries = (data, entries = {}, baseKey = '') => {
        Object.entries(data).forEach(([iKey, iValue]) => {
            const key = baseKey === '' ? iKey : `${baseKey}[${iKey}]`;
            if ('' === baseKey && isValueEmpty(iValue)) {
                entries[key] = '';
            }
            else if (null !== iValue) {
                if (typeof iValue === 'object') {
                    entries = Object.assign(Object.assign({}, entries), buildQueryStringEntries(iValue, entries, key));
                }
                else {
                    entries[key] = encodeURIComponent(iValue)
                        .replace(/%20/g, '+')
                        .replace(/%2C/g, ',');
                }
            }
        });
        return entries;
    };
    const entries = buildQueryStringEntries(data);
    return Object.entries(entries)
        .map(([key, value]) => `${key}=${value}`)
        .join('&');
}
function fromQueryString(search) {
    search = search.replace('?', '');
    if (search === '')
        return {};
    const insertDotNotatedValueIntoData = (key, value, data) => {
        const [first, second, ...rest] = key.split('.');
        if (!second)
            return (data[key] = value);
        if (data[first] === undefined) {
            data[first] = Number.isNaN(Number.parseInt(second)) ? {} : [];
        }
        insertDotNotatedValueIntoData([second, ...rest].join('.'), value, data[first]);
    };
    const entries = search.split('&').map((i) => i.split('='));
    const data = {};
    entries.forEach(([key, value]) => {
        value = decodeURIComponent(value.replace(/\+/g, '%20'));
        if (!key.includes('[')) {
            data[key] = value;
        }
        else {
            if ('' === value)
                return;
            const dotNotatedKey = key.replace(/\[/g, '.').replace(/]/g, '');
            insertDotNotatedValueIntoData(dotNotatedKey, value, data);
        }
    });
    return data;
}
class UrlUtils extends URL {
    has(key) {
        const data = this.getData();
        return Object.keys(data).includes(key);
    }
    set(key, value) {
        const data = this.getData();
        data[key] = value;
        this.setData(data);
    }
    get(key) {
        return this.getData()[key];
    }
    remove(key) {
        const data = this.getData();
        delete data[key];
        this.setData(data);
    }
    getData() {
        if (!this.search) {
            return {};
        }
        return fromQueryString(this.search);
    }
    setData(data) {
        this.search = toQueryString(data);
    }
}
class HistoryStrategy {
    static replace(url) {
        history.replaceState(history.state, '', url);
    }
}

class QueryStringPlugin {
    constructor(mapping) {
        this.mapping = mapping;
    }
    attachToComponent(component) {
        component.on('render:finished', (component) => {
            const urlUtils = new UrlUtils(window.location.href);
            const currentUrl = urlUtils.toString();
            Object.entries(this.mapping).forEach(([prop, mapping]) => {
                const value = component.valueStore.get(prop);
                urlUtils.set(mapping.name, value);
            });
            if (currentUrl !== urlUtils.toString()) {
                HistoryStrategy.replace(urlUtils);
            }
        });
    }
}

const getComponent = (element) => LiveControllerDefault.componentRegistry.getComponent(element);
class LiveControllerDefault extends Controller {
    constructor() {
        super(...arguments);
        this.pendingActionTriggerModelElement = null;
        this.elementEventListeners = [
            { event: 'input', callback: (event) => this.handleInputEvent(event) },
            { event: 'change', callback: (event) => this.handleChangeEvent(event) },
            { event: 'live:connect', callback: (event) => this.handleConnectedControllerEvent(event) },
        ];
        this.pendingFiles = {};
    }
    initialize() {
        this.handleDisconnectedChildControllerEvent = this.handleDisconnectedChildControllerEvent.bind(this);
        const id = this.element.dataset.liveId || null;
        this.component = new Component(this.element, this.nameValue, this.propsValue, this.listenersValue, (currentComponent, onlyParents, onlyMatchName) => LiveControllerDefault.componentRegistry.findComponents(currentComponent, onlyParents, onlyMatchName), this.fingerprintValue, id, new Backend(this.urlValue, this.requestMethodValue, this.csrfValue), new StandardElementDriver());
        this.proxiedComponent = proxifyComponent(this.component);
        this.element.__component = this.proxiedComponent;
        if (this.hasDebounceValue) {
            this.component.defaultDebounce = this.debounceValue;
        }
        const plugins = [
            new LoadingPlugin(),
            new ValidatedFieldsPlugin(),
            new PageUnloadingPlugin(),
            new PollingPlugin(),
            new SetValueOntoModelFieldsPlugin(),
            new QueryStringPlugin(this.queryMappingValue),
        ];
        plugins.forEach((plugin) => {
            this.component.addPlugin(plugin);
        });
    }
    connect() {
        LiveControllerDefault.componentRegistry.registerComponent(this.element, this.component);
        this.component.connect();
        this.elementEventListeners.forEach(({ event, callback }) => {
            this.component.element.addEventListener(event, callback);
        });
        this.dispatchEvent('connect');
    }
    disconnect() {
        LiveControllerDefault.componentRegistry.unregisterComponent(this.component);
        this.component.disconnect();
        this.elementEventListeners.forEach(({ event, callback }) => {
            this.component.element.removeEventListener(event, callback);
        });
        this.dispatchEvent('disconnect');
    }
    update(event) {
        if (event.type === 'input' || event.type === 'change') {
            throw new Error(`Since LiveComponents 2.3, you no longer need data-action="live#update" on form elements. Found on element: ${getElementAsTagText(event.currentTarget)}`);
        }
        this.updateModelFromElementEvent(event.currentTarget, null);
    }
    action(event) {
        const rawAction = event.currentTarget.dataset.actionName;
        const directives = parseDirectives(rawAction);
        let debounce = false;
        directives.forEach((directive) => {
            let pendingFiles = {};
            const validModifiers = new Map();
            validModifiers.set('prevent', () => {
                event.preventDefault();
            });
            validModifiers.set('stop', () => {
                event.stopPropagation();
            });
            validModifiers.set('self', () => {
                if (event.target !== event.currentTarget) {
                    return;
                }
            });
            validModifiers.set('debounce', (modifier) => {
                debounce = modifier.value ? parseInt(modifier.value) : true;
            });
            validModifiers.set('files', (modifier) => {
                if (!modifier.value) {
                    pendingFiles = this.pendingFiles;
                }
                else if (this.pendingFiles[modifier.value]) {
                    pendingFiles[modifier.value] = this.pendingFiles[modifier.value];
                }
            });
            directive.modifiers.forEach((modifier) => {
                var _a;
                if (validModifiers.has(modifier.name)) {
                    const callable = (_a = validModifiers.get(modifier.name)) !== null && _a !== void 0 ? _a : (() => { });
                    callable(modifier);
                    return;
                }
                console.warn(`Unknown modifier ${modifier.name} in action "${rawAction}". Available modifiers are: ${Array.from(validModifiers.keys()).join(', ')}.`);
            });
            for (const [key, input] of Object.entries(pendingFiles)) {
                if (input.files) {
                    this.component.files(key, input);
                }
                delete this.pendingFiles[key];
            }
            this.component.action(directive.action, directive.named, debounce);
            if (getModelDirectiveFromElement(event.currentTarget, false)) {
                this.pendingActionTriggerModelElement = event.currentTarget;
            }
        });
    }
    $render() {
        return this.component.render();
    }
    emit(event) {
        this.getEmitDirectives(event).forEach(({ name, data, nameMatch }) => {
            this.component.emit(name, data, nameMatch);
        });
    }
    emitUp(event) {
        this.getEmitDirectives(event).forEach(({ name, data, nameMatch }) => {
            this.component.emitUp(name, data, nameMatch);
        });
    }
    emitSelf(event) {
        this.getEmitDirectives(event).forEach(({ name, data }) => {
            this.component.emitSelf(name, data);
        });
    }
    getEmitDirectives(event) {
        const element = event.currentTarget;
        if (!element.dataset.event) {
            throw new Error(`No data-event attribute found on element: ${getElementAsTagText(element)}`);
        }
        const eventInfo = element.dataset.event;
        const directives = parseDirectives(eventInfo);
        const emits = [];
        directives.forEach((directive) => {
            let nameMatch = null;
            directive.modifiers.forEach((modifier) => {
                switch (modifier.name) {
                    case 'name':
                        nameMatch = modifier.value;
                        break;
                    default:
                        throw new Error(`Unknown modifier ${modifier.name} in event "${eventInfo}".`);
                }
            });
            emits.push({
                name: directive.action,
                data: directive.named,
                nameMatch,
            });
        });
        return emits;
    }
    $updateModel(model, value, shouldRender = true, debounce = true) {
        return this.component.set(model, value, shouldRender, debounce);
    }
    handleInputEvent(event) {
        const target = event.target;
        if (!target) {
            return;
        }
        this.updateModelFromElementEvent(target, 'input');
    }
    handleChangeEvent(event) {
        const target = event.target;
        if (!target) {
            return;
        }
        this.updateModelFromElementEvent(target, 'change');
    }
    updateModelFromElementEvent(element, eventName) {
        var _a;
        if (!elementBelongsToThisComponent(element, this.component)) {
            return;
        }
        if (!(element instanceof HTMLElement)) {
            throw new Error('Could not update model for non HTMLElement');
        }
        if (element instanceof HTMLInputElement && element.type === 'file') {
            const key = element.name;
            if ((_a = element.files) === null || _a === void 0 ? void 0 : _a.length) {
                this.pendingFiles[key] = element;
            }
            else if (this.pendingFiles[key]) {
                delete this.pendingFiles[key];
            }
        }
        const modelDirective = getModelDirectiveFromElement(element, false);
        if (!modelDirective) {
            return;
        }
        const modelBinding = getModelBinding(modelDirective);
        if (!modelBinding.targetEventName) {
            modelBinding.targetEventName = 'input';
        }
        if (this.pendingActionTriggerModelElement === element) {
            modelBinding.shouldRender = false;
        }
        if (eventName === 'change' && modelBinding.targetEventName === 'input') {
            modelBinding.targetEventName = 'change';
        }
        if (eventName && modelBinding.targetEventName !== eventName) {
            return;
        }
        if (false === modelBinding.debounce) {
            if (modelBinding.targetEventName === 'input') {
                modelBinding.debounce = true;
            }
            else {
                modelBinding.debounce = 0;
            }
        }
        const finalValue = getValueFromElement(element, this.component.valueStore);
        this.component.set(modelBinding.modelName, finalValue, modelBinding.shouldRender, modelBinding.debounce);
    }
    handleConnectedControllerEvent(event) {
        if (event.target === this.element) {
            return;
        }
        const childController = event.detail.controller;
        if (childController.component.getParent()) {
            return;
        }
        const modelDirectives = getAllModelDirectiveFromElements(childController.element);
        const modelBindings = modelDirectives.map(getModelBinding);
        this.component.addChild(childController.component, modelBindings);
        childController.element.addEventListener('live:disconnect', this.handleDisconnectedChildControllerEvent);
    }
    handleDisconnectedChildControllerEvent(event) {
        const childController = event.detail.controller;
        childController.element.removeEventListener('live:disconnect', this.handleDisconnectedChildControllerEvent);
        if (childController.component.getParent() !== this.component) {
            return;
        }
        this.component.removeChild(childController.component);
    }
    dispatchEvent(name, detail = {}, canBubble = true, cancelable = false) {
        detail.controller = this;
        detail.component = this.proxiedComponent;
        this.dispatch(name, { detail, prefix: 'live', cancelable, bubbles: canBubble });
    }
}
LiveControllerDefault.values = {
    name: String,
    url: String,
    props: Object,
    csrf: String,
    listeners: { type: Array, default: [] },
    debounce: { type: Number, default: 150 },
    id: String,
    fingerprint: { type: String, default: '' },
    requestMethod: { type: String, default: 'post' },
    queryMapping: { type: Object, default: {} },
};
LiveControllerDefault.componentRegistry = new ComponentRegistry();

export { Component, LiveControllerDefault as default, getComponent };
