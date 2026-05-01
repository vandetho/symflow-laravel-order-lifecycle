import './bootstrap';
import mermaid from 'mermaid';

mermaid.initialize({
    startOnLoad: false,
    theme: 'base',
    themeVariables: {
        fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
        primaryColor: '#f4f4f5',
        primaryTextColor: '#18181b',
        primaryBorderColor: '#d4d4d8',
        lineColor: '#a1a1aa',
        tertiaryColor: '#fafafa',
    },
    flowchart: { curve: 'basis' },
});

window.renderMermaid = async function (selector = '.mermaid') {
    const nodes = document.querySelectorAll(selector);
    for (const node of nodes) {
        if (node.dataset.processed === '1') continue;
        const source = node.dataset.source ?? node.textContent;
        const id = 'm-' + Math.random().toString(36).slice(2, 9);
        try {
            const { svg } = await mermaid.render(id, source);
            node.innerHTML = svg;
            node.dataset.processed = '1';
        } catch (e) {
            node.innerHTML = '<div class="rounded-md bg-rose-50 p-3 text-sm text-rose-700">Diagram failed to render: ' + e.message + '</div>';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => window.renderMermaid());
document.addEventListener('livewire:navigated', () => window.renderMermaid());
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', () => {
        document.querySelectorAll('.mermaid').forEach((n) => (n.dataset.processed = '0'));
        window.renderMermaid();
    });
});
