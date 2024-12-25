<?php


// May 2018

// https://www.w3.org/TR/svg-aam-1.0/#mapping_additional_nd

/**
 * When computing an accessible name or accessible description, user agents MUST conform to the section titled Text
 * Alternative Computation of the Accessible Name and Description specification [ACCNAME-AAM], with the following
 * modifications for the SVG host language:
 */

/**
 *
 *
 * The net effect of these changes is to establish the following priority of alternative text values for the accessible
 * name:
 *
 * aria-labelledby
 * aria-label
 * a direct child title element
 * xlink:title attribute on a link
 * for text container elements, the text content
 *
 * The alternative text values for the accessible description have the following priority:
 *
 * aria-describedby
 * a direct child desc element
 * for text container elements, the text content
 * a direct child title element that provides a tooltip, when ARIA label attributes are used to provide the accessible
 * name xlink:title attribute on a link, if not used to provide the accessible name
 *
 * The aria-labelledby and aria-describedby properties can reference the element on which they are given, in order to
 * concatenate one of the other text alternatives with text from a separate element.
 */
