{{-- Accordion sidebar: opening a navigation group collapses all the others. --}}
<script>
    (function () {
        if (window.__sidebarAccordion) return
        window.__sidebarAccordion = true

        const groups = () =>
            Array.from(document.querySelectorAll('.fi-sidebar-group[data-group-label]')).map((el) => el.dataset.groupLabel)

        const activeGroup = () => {
            const active = document.querySelector('.fi-sidebar-group.fi-active[data-group-label]')
            return active ? active.dataset.groupLabel : null
        }

        // Keep only `keep` open (or everything closed when there is none).
        const openOnly = (store, keep) => {
            store.collapsedGroups = groups().filter((label) => label !== keep)
        }

        const patch = () => {
            const store = window.Alpine && window.Alpine.store('sidebar')
            if (!store) return false

            if (!store.__accordion) {
                store.__accordion = true
                const original = store.toggleCollapsedGroup.bind(store)

                store.toggleCollapsedGroup = function (group) {
                    if (this.groupIsCollapsed(group)) {
                        openOnly(this, group)
                    } else {
                        original(group)
                    }
                }
            }

            return true
        }

        const sync = () => {
            if (!patch()) return
            const store = window.Alpine.store('sidebar')
            openOnly(store, activeGroup())
        }

        const start = () => {
            let tries = 0
            const timer = setInterval(() => {
                if (patch() || ++tries > 40) {
                    clearInterval(timer)
                    sync()
                }
            }, 50)
        }

        document.addEventListener('livewire:navigated', () => requestAnimationFrame(sync))
        document.addEventListener('alpine:initialized', start)
        start()
    })()
</script>
