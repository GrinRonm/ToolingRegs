document.addEventListener('DOMContentLoaded', () => {
    const btnCompare = document.getElementById('btn-compare');
    const textOld = document.getElementById('text-old');
    const textNew = document.getElementById('text-new');
    const output = document.getElementById('diff-output');

    btnCompare.addEventListener('click', () => {
        const str1 = textOld.value;
        const str2 = textNew.value;

        if (!str1 && !str2) {
            output.innerHTML = '<span class="diff-grey">Оба поля пусты.</span>';
            return;
        }

        if (typeof Diff === 'undefined') {
            showToast('Библиотека для сравнения не загружена', 'error');
            return;
        }

        const diff = Diff.diffLines(str1, str2);
        const fragment = document.createDocumentFragment();

        diff.forEach((part) => {
            const span = document.createElement('span');
            span.textContent = part.value;
            
            if (part.added) {
                span.className = 'diff-added';
            } else if (part.removed) {
                span.className = 'diff-removed';
            } else {
                span.className = 'diff-grey';
            }
            
            fragment.appendChild(span);
        });

        output.innerHTML = '';
        output.appendChild(fragment);
    });
});
