/**
 * @package social-icon-block-variations
 * @author Cooper Dalrymple
 * @license gplv3-or-later
 * @version 1.0.0
 * @since 1.0.0
 */

const { registerBlockVariation } = wp.blocks;
const { __ } = wp.i18n;
const { SVG, Path } = wp.primitives;
const { jsx } = ReactJSXRuntime;

gsiv_icons.forEach((variation) => {
    const { name, title, width, height, viewBox, path } = variation;
    registerBlockVariation('core/social-link', {
        name: name,
        attributes: {
            service: name
        },
        title: title,
        icon: () => (0, jsx)(SVG, {
            width: width,
            height: height,
            viewBox: viewBox,
            version: "1.1",
            children: (0, jsx)(Path, {
                d: path
            })
        }),
        isActive: (blockAttributes, variationAttributes) =>
            blockAttributes.service === variationAttributes.service,
    });
});
