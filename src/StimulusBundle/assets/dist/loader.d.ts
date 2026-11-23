import { loadControllers } from "./runtime.js";
import { Application } from "@hotwired/stimulus";
declare const startStimulusApp: () => Application;
export { loadControllers, startStimulusApp };