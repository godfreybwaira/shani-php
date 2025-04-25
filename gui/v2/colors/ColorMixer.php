<?php

/**
 * Description of ColorMixer
 * @author coder
 *
 * Created on: Apr 21, 2025 at 7:54:53 PM
 */

namespace gui\v2\colors {

    final class ColorMixer
    {

        /**
         * Check whether hexadecimal color is dark or not
         * @param string $color Hexadecimal color
         * @return bool
         */
        public static function isDark(string $color): bool
        {
            $green = self::green($color);
            $blue = self::blue($color);
            $red = self::red($color);
            $g = self::gammaCorrection($green / 255);
            $b = self::gammaCorrection($blue / 255);
            $r = self::gammaCorrection($red / 255);
            $luminance = $r * 0.2126 + $g * 0.7152 + $b * 0.0722;
            return $luminance < 0.179;
        }

        private static function gammaCorrection(float $val): float
        {
            return $val <= 0.03928 ? $val / 12.92 : pow(($val + 0.055) / 1.055, 2.4);
        }

        private static function red(string $color): int
        {
            return hexdec(substr($color, 1, 2));
        }

        private static function green(string $color): int
        {
            return hexdec(substr($color, 3, 2));
        }

        private static function blue(string $color): int
        {
            return hexdec(substr($color, 5, 2));
        }
    }

}
/**
 * (function () {
    'use strict';
    let HUE = null;
    document.body.addEventListener('change', function (e) {
        const form = e.target.closest('form');
        const target = e.target.closest('[name]');
        const customize = function (color) {
            const customized = form.querySelector('[name=customized]');
            customized.parentNode.style.backgroundColor = color;
            customized.nextElementSibling.innerText = Theme.toHEX(color);
            const colors = Theme.create(color);
            form.querySelector('[name=colorObj]').value = JSON.stringify(colors);
            if (colors !== null) {
                Theme.render(form.querySelector('#previewColors'), colors);
            }
        };
        if (target.name === 'color') {
            let color = target.value;
            if (color[0] !== '#') {
                color = '#' + color;
            }
            HUE = Theme.hue(color);
            const selected = form.querySelector('#selectedColor');
            selected.style.backgroundColor = color;
            selected.innerHTML = color;
            form.invert.checked = false;
            customize(color);
        } else if (target.type === 'range' || target.name === 'invert') {
            let hue2 = parseFloat(target.value) + HUE + 90;
            if (target.name === 'invert') {
                if (hue2 > 360) {
                    hue2 -= 360;
                }
            }
            target.parentNode.querySelector('span').innerText = target.value;
            customize('hsl(' + hue2 + ',' + form.sat.value + '%,' + form.light.value + '%)');
        }
    });

    const Theme = (function () {
        const fn = {
            create: function (color) {
                const hsl = rawColor(this.toHSL(color)), iterations = 13;
                const median = Math.floor(iterations / 2), factor = Math.ceil(iterations * .5);
                let steps = (100 - hsl[2]) / factor, diff = median * steps;
                if (hsl[2] < diff) {
                    steps = 100 / factor - steps;
                    diff = median * steps;
                }
                const start = hsl[2] + diff, theme = {};
                for (let it = 0, key = null; it < iterations; it++) {
                    const hex = fn.toHEX('hsl(' + hsl[0] + ',' + hsl[1] + '%,' + (start - steps * it) + '%)');
                    if (it < median) {
                        key = 'l' + (median - it);
                    } else if (it > median) {
                        key = 'd' + (it - median);
                    } else {
                        key = 'theme';
                    }
                    theme[key] = {
                        bg: hex, txt: isDark(hex, 145) ? '#fff' : '#000'
                    };
                }
                setRange(hsl);
                return theme;
            },
            toHEX: function (color) {
                const type = getType(color);
                if (type === 'HEX') {
                    return color;
                }
                if (type === 'RGB') {
                    const raw = [], values = rawColor(color);
                    for (let i = 0; i < 3 && values[i] < 256; i++) {
                        const num = new Number(Math.round(values[i])).toString(16);
                        raw.push(num.length === 1 ? '0' + num : num);
                    }
                    return '#' + raw.join('');
                }
                if (type === 'HSL') {
                    return this.toHEX(this.toRGB(color));
                }
                return null;
            },
            toRGB: function (color) {
                const type = getType(color);
                if (type === 'HEX') {
                    const hex = color.substr(1), raw = [], steps = Math.floor(color.length / 3);
                    for (let i = 0; i < hex.length; i += steps) {
                        const num = hex.substr(i, steps);
                        raw.push(new Number('0x' + (num.length === 1 ? num + num : num)).toString());
                    }
                    return 'rgb(' + raw.join(',') + ')';
                }
                if (type === 'HSL') {
                    const values = rawColor(color);
                    const hue = values[0] / 60, saturation = values[1] / 100, lightness = values[2] / 100;
                    const val1 = (1 - Math.abs(2 * lightness - 1)) * saturation;
                    const val2 = val1 * (1 - Math.abs(hue % 2 - 1)), val3 = lightness - val1 / 2;
                    let r = 0, g = 0, b = 0;
                    if (hue < 1) {
                        r = val1;
                        g = val2;
                    } else if (hue < 2) {
                        g = val1;
                        r = val2;
                    } else if (hue < 3) {
                        g = val1;
                        b = val2;
                    } else if (hue < 4) {
                        b = val1;
                        g = val2;
                    } else if (hue < 5) {
                        b = val1;
                        r = val2;
                    } else if (hue < 6) {
                        r = val1;
                        b = val2;
                    }
                    const rgb = [
                        Math.round((r + val3) * 255), Math.round((g + val3) * 255),
                        Math.round((b + val3) * 255)
                    ];
                    return 'rgb(' + rgb.join(',') + ')';
                }
                if (type === 'RGB') {
                    return color;
                }
                return null;
            },
            toHSL: function (color) {
                const type = getType(color);
                if (type === 'RGB') {
                    const values = rawColor(color);
                    const red = values[0] / 255, green = values[1] / 255, blue = values[2] / 255;
                    const maxVal = Math.max(red, green, blue), minVal = Math.min(red, green, blue);
                    const diff = maxVal - minVal, lightness = (maxVal + minVal) / 2;
                    let saturation = 0, hue = 0;
                    if (diff > 0) {
                        saturation = diff / (1 - Math.abs(2 * lightness - 1));
                        if (red === maxVal) {
                            hue = 60 * (((green - blue) / diff) % 6);
                        } else if (green === maxVal) {
                            hue = 60 * (2 + ((blue - red) / diff));
                        } else {
                            hue = 60 * (4 + ((red - green) / diff));
                        }
                    }
                    return 'hsl(' + hue + ',' + (saturation * 100) + '%,' + (lightness * 100) + '%)';
                }
                if (type === 'HEX') {
                    return this.toHSL(this.toRGB(color));
                }
                if (type === 'HSL') {
                    return color;
                }
                return null;
            },
            render: function (container, colors) {
                container.innerHTML = null;
                const keys = Object.keys(colors);
                for (let i in keys) {
                    const label = document.createElement('li');
                    label.classList.add('align-right');
                    label.innerText = '.color-' + keys[i];

                    const list = document.createElement('li');
                    list.style.backgroundColor = colors[keys[i]].bg;
                    list.style.color = colors[keys[i]].txt;
                    list.innerText = colors[keys[i]].bg;

                    const ul = document.createElement('ul');
                    ul.style.fontFamily = 'monospace';
                    ul.classList.add('list', 'list-h');
                    ul.appendChild(label);
                    ul.appendChild(list);

                    container.appendChild(ul);
                }
            },
            hue: function (color) {
                return rawColor(this.toHSL(color))[0];
            },
            saturation: function (color) {
                return rawColor(this.toHSL(color))[1];
            },
            lightness: function (color) {
                return rawColor(this.toHSL(color))[2];
            },
            red: function (color) {
                return rawColor(this.toRGB(color))[0];
            },
            green: function (color) {
                return rawColor(this.toRGB(color))[1];
            },
            blue: function (color) {
                return rawColor(this.toRGB(color))[2];
            }
        };
        const setRange = function (color) {
            const hue = document.querySelector('[name=hue]');
            const sat = document.querySelector('[name=sat]');
            const light = document.querySelector('[name=light]');
            hue.value = color[0];
            sat.value = color[1];
            light.value = color[2];
            hue.parentNode.querySelector('span').innerText = hue.value;
            sat.parentNode.querySelector('span').innerText = sat.value;
            light.parentNode.querySelector('span').innerText = light.value;
        };
        const getType = function (color) {
            const type = color.substr(0, 3);
            if (type[0] === '#')
                return 'HEX';
            return type.toUpperCase();
        };
        const rawColor = function (color) {
            let cl = color.substr(color.indexOf('(') + 1);
            if (getType(color) === 'HSL') {
                return cl.substr(0, cl.indexOf(')')).replaceAll('%', '').split(',').map(function (x) {
                    const n = parseFloat(x);
                    return n < 0 ? 360 + n : n;
                });
            }
            return cl.substr(0, cl.indexOf(')')).split(',').map(function (x) {
                return parseFloat(x);
            });
        };
        const isDark = function (color, val) {
//            128<=val<=145
            const values = rawColor(fn.toRGB(color));
//            return Math.sqrt(values[0] * values[0] * .299 + values[1] * values[1] * .587 + values[2] * values[2] * .114) < val;
            return (values[0] * .299 + values[1] * .587 + values[2] * .114) < val;
        };
        return fn;
    })();
})();
 */