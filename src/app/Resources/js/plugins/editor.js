if (window.enableEditors == true){
    window.DCMS.editors = function(){
        window.hasLoaded('axios',function(){
            axios.post('/dcms/content/authenticate', {}, Object.assign(window.axiosCfg, {})).then(function (response) {
                if (response.status == 200){
                    window.hasLoaded('tinymce',function(){
                        var dcmsCount = 0, elementContent, elementUID, elementDisplay, elForm, elEditor, elBtnsDiv, elCancelButton, elClearButton,
                        elSaveButton, btn, editorValue, elRightBtnsDiv, elLeftBtnsDiv;
                        function AssignEditors(){
                            document.querySelectorAll('dcms').forEach(function(element){
                                element.addEventListener('click',function(event){
                                    event.preventDefault();
                                    // Only one editor can be active at the same time
                                    if (document.querySelectorAll('.dcms-editor').length > 0){
                                        return false;
                                    }
    
                                    dcmsCount++;
                                    element = event.target;
    
                                    // Find parent element which spawns the editor, if user has clicked on a child element inside of it
                                    if (!element.dataset.uid){
                                        element = element.closest("dcms");
                                    }
    
                                    elementContent = element.outerHTML;
                                    elementUID = element.dataset.uid;
                                    elementDisplay = element.style.display;
                                    element.style.display = 'none';
    
                                    // Create DCMS editor
    
                                    // Form
                                    elForm = document.createElement('form');
                                    elForm.classList.add('dcms-editor');
                                    elForm.style.display = 'none';
                                    // Textarea
                                    elEditor = document.createElement('textarea');
                                    elEditor.value = elementContent;
                                    elEditor.id = elementUID+dcmsCount;
                                    elForm.appendChild(elEditor, elForm.nextSibling);
                                    // Buttons Div
                                    elRightBtnsDiv = document.createElement('div');
                                    elRightBtnsDiv.classList.add('d-flex');
                                    elRightBtnsDiv.classList.add('justify-content-end');
                                    elRightBtnsDiv.classList.add('w-75');
                                    elLeftBtnsDiv = document.createElement('div');
                                    elLeftBtnsDiv.classList.add('d-flex');
                                    elLeftBtnsDiv.classList.add('justify-content-start');
                                    elLeftBtnsDiv.classList.add('w-25');
                                    // Clear Button
                                    elClearButton = document.createElement('button');
                                    elClearButton.classList.add('btn');
                                    elClearButton.classList.add('btn-danger');
                                    elClearButton.textContent = Lang('Clear');
                                    elLeftBtnsDiv.classList.add('mt-2');
                                    elLeftBtnsDiv.appendChild(elClearButton, elForm.nextSibling);
                                    // Cancel Button
                                    elCancelButton = document.createElement('button');
                                    elCancelButton.classList.add('btn');
                                    elCancelButton.classList.add('btn-warning');
                                    elCancelButton.classList.add('mr-2');
                                    elCancelButton.textContent = Lang('Cancel');
                                    elRightBtnsDiv.appendChild(elCancelButton, elForm.nextSibling);
                                    // Save Button
                                    elSaveButton = document.createElement('button');
                                    elSaveButton.classList.add('btn');
                                    elSaveButton.classList.add('btn-primary');
                                    elSaveButton.textContent = Lang('Save');
                                    elRightBtnsDiv.classList.add('mt-1');
                                    elRightBtnsDiv.appendChild(elSaveButton, elForm.nextSibling);
    
                                    elBtnsDiv = document.createElement('div');
                                    elBtnsDiv.classList.add('d-flex');
                                    elBtnsDiv.appendChild(elLeftBtnsDiv);
                                    elBtnsDiv.appendChild(elRightBtnsDiv);
    
                                    // Insert elements in editor
                                    elForm.appendChild(elBtnsDiv, elForm.nextSibling);
                                    element.parentNode.insertBefore(elForm, element.nextSibling);
    
                                    // Initialise tinymce
                                    tinymce.init({
                                        selector: "#"+elEditor.id,
                                        language_url: window.langFiles,
                                        language: window.locale,
                                        plugins: window.tinyMCEplugins,
                                        toolbar1: window.tinyMCEtoolbar,
                                        force_br_newlines : true,
                                        force_p_newlines : false,
                                        forced_root_block : '',
                                        relative_urls : false,
                                        remove_script_host : false,
                                        convert_urls : true,
                                        end_container_on_empty_block: true,
                                        init_instance_callback: function (editor) {
                                            $(editor.getContainer()).find('button.tox-statusbar__wordcount').click();  // if you use jQuery
                                        }
                                    });
    
                                    setTimeout(() => { elForm.style.display = 'initial'; }, 1000);
    
                                    // Destroy TinyMCE instance
                                    function DestroyInstance(){
                                        tinymce.execCommand('mceRemoveControl', true, elEditor.id);
                                        elForm.parentNode.removeChild(elForm);
                                        element.style.display = elementDisplay;
                                        elForm = null;
                                    }
    
                                    // Insert new content if it has been submitted succesfully
                                    function ReplaceContent(element,newContent){
                                        element.outerHTML = `<dcms data-uid="`+elementUID+`">`+newContent+`</dcms>`;
                                    }
    
                                    // On cancel
                                    elCancelButton.addEventListener('click',function(event){
                                        event.preventDefault();
                                        DestroyInstance();
                                    });
    
                                    // On clear
                                    elClearButton.addEventListener('click',function(event){
                                        event.preventDefault();
                                        btn = event.target;
    
                                        axios.post('/dcms/content/clear', {
                                            contentUID: elementUID
                                        }, Object.assign(window.axiosCfg, {})).then(function (response) {
                                            if (response.status == 200){
                                                location.reload();
                                            } else {
                                                Swal.fire({
                                                    title: Lang('Unknown error'),
                                                    html: Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.'),
                                                    icon: "error"
                                                });
                                            }
                                        });
                                    });
    
                                    // On save
                                    elSaveButton.addEventListener('click',function(event){
                                        event.preventDefault();
                                        btn = event.target;
                                        tinyMCE.triggerSave();
                                        editorValue = tinymce.get(elEditor.id).getContent();
    
                                        axios.post('/dcms/content/update', {
                                            contentUID: elementUID,
                                            contentValue: editorValue
                                        }, Object.assign(window.axiosCfg, {})).then(function (response) {
                                            DestroyInstance();
                                            if (response.status == 200){
                                                ReplaceContent(element,editorValue);
                                                // Callback function (since new HTML is added to the DOM)
                                                AssignEditors();
                                            } else {
                                                Swal.fire({
                                                    title: Lang('Unknown error'),
                                                    html: Lang('An unknown error has occurred.') + "<br>" + Lang('Contact support if this problem persists.'),
                                                    icon: "error"
                                                });
                                            }
                                        });
                                    });
                                });
                            });
                        }
                        AssignEditors();
                    });
                }
            });
        });
    };
    window.DCMS.editors();
}
