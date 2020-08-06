$.getScript('https://unpkg.com/jspdf@1.5.3/dist/jspdf.min.js',function(){
    $.getScript('https://unpkg.com/jspdf-autotable@3.5.6/dist/jspdf.plugin.autotable.js',function(){
        window.JsPDF = function(pdfHeaders=null,pdfRows=null,fileName='export.pdf',print=false){
            const doc = new jsPDF();
            
            doc.fromHtml({
              head: [pdfHeaders],
              body: pdfRows,
            })

            if (print == true){
                doc.autoPrint({variant: 'non-conform'});
            }

            doc.save(fileName);
        }
    });
});
