// Extra capabilities for Filament's TipTap rich editor (WordPress-style):
// font size, block text direction, an alignment class on images and a title
// on links. Plain ES module, no build step — Filament exposes TipTap's core to
// custom extensions on window.FilamentRichEditor.tiptap. Only DECLARED
// attributes survive in TipTap, so they are declared here; the server-side
// twin is App\Support\TipTap\{FontSize,SiteAttributes}.

const { Extension, Mark } = window.FilamentRichEditor.tiptap.core

const FontSize = Mark.create({
    name: 'fontSize',

    addAttributes() {
        return {
            size: {
                default: null,
                parseHTML: (element) => element.style.fontSize || null,
                renderHTML: (attributes) =>
                    attributes.size ? { style: `font-size: ${attributes.size}` } : {},
            },
        }
    },

    parseHTML() {
        return [
            {
                style: 'font-size',
                getAttrs: (value) => (value ? { size: value } : false),
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', HTMLAttributes, 0]
    },

    addCommands() {
        return {
            setFontSize:
                (size) =>
                ({ chain }) =>
                    chain().setMark(this.name, { size }).run(),
            unsetFontSize:
                () =>
                ({ chain }) =>
                    chain().unsetMark(this.name).run(),
        }
    },
})

const simpleAttribute = (name) => ({
    default: null,
    parseHTML: (element) => element.getAttribute(name),
    renderHTML: (attributes) => (attributes[name] ? { [name]: attributes[name] } : {}),
})

export default () =>
    Extension.create({
        name: 'siteWp',

        addExtensions() {
            return [FontSize]
        },

        addGlobalAttributes() {
            return [
                {
                    types: ['paragraph', 'heading', 'listItem', 'blockquote'],
                    attributes: { dir: simpleAttribute('dir') },
                },
                { types: ['image'], attributes: { class: simpleAttribute('class') } },
                { types: ['link'], attributes: { title: simpleAttribute('title') } },
            ]
        },
    })
