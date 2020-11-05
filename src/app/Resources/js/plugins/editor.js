hasLoaded('tinymce',function(){
    var dcmsCount = 0, elementContent, elementUID, elementDisplay, elForm, elEditor, elBtnsDiv, elCancelButton,
    elSaveButton, btn, editorValue, currentTiny, keyUps = 0, tagIsPresent, previousValue, previousFilledValue;
    function AssignEditors(){
        document.querySelectorAll('dcms').forEach(function(element){
            element.addEventListener('click',function(event){
                // Only one editor can be active at the same time
                if (document.querySelectorAll('.dcms-editor').length > 0){
                    return false;
                }

                dcmsCount++;
                element = event.target;

                // Find parent element which spawns the editor, if user has clicked on a child element inside of it
                if (!element.dataset.uid){
                    element = element.closest("dcms")
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
                elEditor.value = elementContent
                elEditor.id = elementUID+dcmsCount;
                elForm.appendChild(elEditor, elForm.nextSibling);
                // Buttons Div
                elBtnsDiv = document.createElement('div');
                elBtnsDiv.classList.add('d-flex');
                elBtnsDiv.classList.add('justify-content-end');
                // Cancel Button
                elCancelButton = document.createElement('button');
                elCancelButton.classList.add('btn');
                elCancelButton.classList.add('btn-warning');
                elCancelButton.classList.add('mr-2');
                elCancelButton.textContent = Lang('Cancel');
                elBtnsDiv.appendChild(elCancelButton, elForm.nextSibling);
                // Save Button
                elSaveButton = document.createElement('button');
                elSaveButton.classList.add('btn');
                elSaveButton.classList.add('btn-primary');
                elSaveButton.textContent = Lang('Save');
                elBtnsDiv.classList.add('mt-2');
                elBtnsDiv.appendChild(elSaveButton, elForm.nextSibling);

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
                    height: '275px',
                    force_br_newlines : true,
                    force_p_newlines : false,
                    forced_root_block : '',
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

                // On save
                elSaveButton.addEventListener('click',function(event){
                    event.preventDefault();
                    btn = event.target;
                    tinyMCE.triggerSave();
                    editorValue = tinymce.get(elEditor.id).getContent();

                    async function saveContent() {
                        await axios.post('/dcms/update/content', {
                            contentUID: elementUID,
                            contentValue: editorValue
                        }, Object.assign(axiosCfg, {})).then(function (response) {
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
                                })
                            }
                        });
                    }
                    saveContent();
                });
            })
        });
    }
    AssignEditors();
})