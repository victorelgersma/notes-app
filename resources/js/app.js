import 'trix';
import 'trix/dist/trix.css';

// Notes are text-only: no file/image attachments. Hide the paperclip
// button and refuse anything dropped or pasted as a file.
document.addEventListener('trix-file-accept', (e) => e.preventDefault());

document.addEventListener('trix-initialize', (e) => {
    e.target.toolbarElement
        ?.querySelector('.trix-button-group--file-tools')
        ?.remove();
});
