import { PluginInterface } from "./PluginInterface";
import Component from "../index";
export default class implements PluginInterface {
    intersectionObserver: IntersectionObserver;
    constructor();
    attachToComponent(component: Component): void;
}
