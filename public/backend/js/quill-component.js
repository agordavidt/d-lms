
const QuillComponent = (function() {
    'use strict';
    
    // Default configuration
    const DEFAULT_CONFIG = {
        theme: 'snow',
        placeholder: 'Start writing...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        }
    };
    
    // Store editor instances
    const editors = {};
    
    /**
     * Initialize Quill editor
     * 
     * @param {string} editorId - ID of the div element for editor
     * @param {Object} options - Configuration options
     * @returns {Quill} - Quill editor instance
     */
    function init(editorId, options = {}) {
        // Check if Quill is loaded
        if (typeof Quill === 'undefined') {
            console.error('Quill library not loaded. Include Quill CDN before this script.');
            return null;
        }
        
        // Merge options with defaults
        const config = { ...DEFAULT_CONFIG, ...options };
        
        // Get editor element
        const editorElement = document.getElementById(editorId);
        if (!editorElement) {
            console.error(`Element with ID "${editorId}" not found.`);
            return null;
        }
        
        // Initialize Quill
        const quill = new Quill(`#${editorId}`, config);
        
        // Setup image upload handler if uploadUrl provided
        if (options.uploadUrl) {
            setupImageUpload(quill, options.uploadUrl, options.csrfToken);
        }
        
        // Store instance
        editors[editorId] = quill;
        
        return quill;
    }
    
    /**
     * Setup custom image upload handler
     * 
     * @param {Quill} quill - Quill instance
     * @param {string} uploadUrl - Upload endpoint URL
     * @param {string} csrfToken - CSRF token
     */
    function setupImageUpload(quill, uploadUrl, csrfToken) {
        const toolbar = quill.getModule('toolbar');
        toolbar.addHandler('image', function() {
            selectLocalImage(quill, uploadUrl, csrfToken);
        });
    }
    
    /**
     * Handle image file selection and upload
     */
    function selectLocalImage(quill, uploadUrl, csrfToken) {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async () => {
            const file = input.files[0];
            if (!file) return;

            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size should not exceed 2MB');
                return;
            }

            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select a valid image file');
                return;
            }

            // Show loading indicator
            const range = quill.getSelection(true);
            quill.insertText(range.index, 'Uploading image...');
            
            // Upload image
            try {
                const location = await uploadImage(file, uploadUrl, csrfToken);
                
                // Remove loading text
                quill.deleteText(range.index, 'Uploading image...'.length);
                
                // Insert uploaded image
                quill.insertEmbed(range.index, 'image', location);
                quill.setSelection(range.index + 1);
                
            } catch (error) {
                // Remove loading text on error
                quill.deleteText(range.index, 'Uploading image...'.length);
                alert('Failed to upload image: ' + error.message);
            }
        };
    }
    
    /**
     * Upload image to server
     * 
     * @param {File} file - Image file
     * @param {string} uploadUrl - Upload endpoint
     * @param {string} csrfToken - CSRF token
     * @returns {Promise<string>} - Uploaded image URL
     */
    async function uploadImage(file, uploadUrl, csrfToken) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', csrfToken);

        const response = await fetch(uploadUrl, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Upload failed with status: ' + response.status);
        }

        const result = await response.json();

        if (!result.location) {
            throw new Error(result.error || 'Unknown error');
        }

        return result.location;
    }
    
    /**
     * Get editor instance by ID
     * 
     * @param {string} editorId - Editor ID
     * @returns {Quill|null} - Quill instance or null
     */
    function getInstance(editorId) {
        return editors[editorId] || null;
    }
    
    /**
     * Get HTML content from editor
     * 
     * @param {string} editorId - Editor ID
     * @returns {string} - HTML content
     */
    function getContent(editorId) {
        const editor = editors[editorId];
        return editor ? editor.root.innerHTML : '';
    }
    
    /**
     * Set HTML content in editor
     * 
     * @param {string} editorId - Editor ID
     * @param {string} html - HTML content
     */
    function setContent(editorId, html) {
        const editor = editors[editorId];
        if (editor) {
            editor.root.innerHTML = html;
        }
    }
    
    /**
     * Get plain text from editor
     * 
     * @param {string} editorId - Editor ID
     * @returns {string} - Plain text
     */
    function getText(editorId) {
        const editor = editors[editorId];
        return editor ? editor.getText().trim() : '';
    }
    
    /**
     * Bind editor content to hidden input (for forms)
     * 
     * @param {string} editorId - Editor ID
     * @param {string} inputId - Hidden input ID
     */
    function bindToInput(editorId, inputId) {
        const editor = editors[editorId];
        const input = document.getElementById(inputId);
        
        if (!editor || !input) {
            console.error('Editor or input not found');
            return;
        }
        
        // Initial sync
        input.value = editor.root.innerHTML;
        
        // Sync on change
        editor.on('text-change', function() {
            input.value = editor.root.innerHTML;
        });
    }
    
    /**
     * Validate editor has content
     * 
     * @param {string} editorId - Editor ID
     * @returns {boolean} - True if has content
     */
    function hasContent(editorId) {
        const text = getText(editorId);
        return text.length > 0;
    }
    
    /**
     * Clear editor content
     * 
     * @param {string} editorId - Editor ID
     */
    function clear(editorId) {
        const editor = editors[editorId];
        if (editor) {
            editor.setText('');
        }
    }
    
    /**
     * Destroy editor instance
     * 
     * @param {string} editorId - Editor ID
     */
    function destroy(editorId) {
        const editor = editors[editorId];
        if (editor) {
            // Quill doesn't have a destroy method, but we can remove from our tracking
            delete editors[editorId];
        }
    }
    
    // Public API
    return {
        init: init,
        getInstance: getInstance,
        getContent: getContent,
        setContent: setContent,
        getText: getText,
        bindToInput: bindToInput,
        hasContent: hasContent,
        clear: clear,
        destroy: destroy
    };
})();

