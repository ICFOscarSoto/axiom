/**
 * @module ol/interaction/DragZoom
 */
import {easeOut} from '../easing.js';
import {shiftKeyOnly} from '../events/condition.js';
import {createOrUpdateFromCoordinates, getBottomLeft, getCenter, getTopRight, scaleFromCenter} from '../extent.js';
import DragBox from './DragBox.js';


/**
 * @typedef {Object} Options
 * @property {string} [className='ol-dragzoom'] CSS class name for styling the
 * box.
 * @property {import("../events/condition.js").Condition} [condition] A function that
 * takes an {@link module:ol/MapBrowserEvent~MapBrowserEvent} and returns a
 * boolean to indicate whether that event should be handled.
 * Default is {@link module:ol/events/condition~shiftKeyOnly}.
 * @property {number} [duration=200] Animation duration in milliseconds.
 * @property {boolean} [out=false] Use interaction for zooming out.
 */


/**
 * @classdesc
 * Allows the user to zoom the map by clicking and dragging on the map,
 * normally combined with an {@link module:ol/events/condition} that limits
 * it to when a key, shift by default, is held down.
 *
 * To change the style of the box, use CSS and the `.ol-dragzoom` selector, or
 * your custom one configured with `className`.
 * @api
 */
class DragZoom extends DragBox {
  /**
   * @param {Options=} opt_options Options.
   */
  constructor(opt_options) {
    const options = opt_options ? opt_options : {};

    const condition = options.condition ? options.condition : shiftKeyOnly;

    super({
      condition: condition,
      className: options.className || 'ol-dragzoom',
      onBoxEnd: onBoxEnd
    });

    /**
     * @private
     * @type {number}
     */
    this.duration_ = options.duration !== undefined ? options.duration : 200;

    /**
     * @private
     * @type {boolean}
     */
    this.out_ = options.out !== undefined ? options.out : false;
  }
}


/**
 * @this {DragZoom}
 */
function onBoxEnd() {
  const map = this.getMap();
  const view = /** @type {!import("../View.js").default} */ (map.getView());
  const size = /** @type {!import("../size.js").Size} */ (map.getSize());
  let extent = this.getGeometry().getExtent();

  if (this.out_) {
    const mapExtent = view.calculateExtent(size);
    const boxPixelExtent = createOrUpdateFromCoordinates([
      map.getPixelFromCoordinate(getBottomLeft(extent)),
      map.getPixelFromCoordinate(getTopRight(extent))]);
    const factor = view.getResolutionForExtent(boxPixelExtent, size);

    scaleFromCenter(mapExtent, 1 / factor);
    extent = mapExtent;
  }

  const resolution = view.constrainResolution(
    view.getResolutionForExtent(extent, size));

  let center = getCenter(extent);
  center = view.constrainCenter(center);

  view.animate({
    resolution: resolution,
    center: center,
    duration: this.duration_,
    easing: easeOut
  });
}


export default DragZoom;
