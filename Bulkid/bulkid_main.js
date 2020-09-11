/////////////////////////////////////////////////////////////////////////////
//
(function($){
	
	$(document).ready(function()
	{
		
		$("#frm_data").submit(function(e) {
			if($("tr.corona_id_row").length > 0) {
				if(!confirm("Sind die bereits erzeugten ID's bereits verarbeitet und sollen neue angefordert werden? Andernfalls sind diese verloren."))
					e.preventDefault();
			}
			
		});
		
		if($(".navbar_bottom").length > 0)
			$("body").css('margin-bottom',$(".navbar_bottom").outerHeight() +'px');
		
		if($(".navbar_top").length > 0)
			$("body").css('margin-top',$(".navbar_top").outerHeight() +'px');
		
		if($(".timeout_alert").length > 0) {
			setTimeout(function(){
				$(".timeout_alert").remove();
			},5000);
		}
		
		$("button.list_csv").click(function(){
			let data=[];
			
			$("tr.corona_id_row").each(function(idx,el) {
				data.push([$(el).find(".c_id").text(),$(el).find(".c_pin").text()]);
			});
			genCSV(data, "CoronaIDs", false); 
		});
		
		$(".papersize").click(function(e){
			e.preventDefault();
			
			let data=[];
			
			$("tr.corona_id_row").each(function(idx,el) {
				data.push([$(el).find(".c_id").text(),$(el).find(".c_pin").text()]);
			});

			switch(e.currentTarget.id) {
				case "list_a5":
					genPDF(data,"a5");
					break;
					
				case "singlepage_a4":
					genPDFSinglePage(data,"a4");
					break;
					
				case "singlepage_a5":
					genPDFSinglePage(data,"a5");
					break;
					
				case "list_a4":
				default:
					genPDF(data,"a4");
					break;
			}
			
			
			
		});
	});

function genPDFSinglePage(data,papersize) {		
	var doc = new jsPDF({unit: "mm", format: papersize});
	let pgHeight=doc.internal.pageSize.getHeight();
	let pgWidth=doc.internal.pageSize.getWidth();
	
	
	let lg=data.length;

	for(let i=0; i < lg; i++) {
		if(i > 0)
			doc.addPage(papersize);
		
		// doc.setPage(i + 1);
		doc.text("Für die Aktion:",pgWidth/2,15, { align: "center" });
		
		doc.autoTable({
			startY: pgHeight / 4 - 5,
			margin:  {top: 0, right: 0, bottom: 0, left: pgWidth/2 - 15} ,
			tableWidth: 30,
			theme: 'grid',
			headStyles: { fillColor: "#f3f4f4", textColor: 20},
			styles: { halign: "center"},
			head: [['CoronaID']],
			body: [[data[i][0]]],
		});
		
		doc.setLineDash([5, 5], 0);
		doc.setLineWidth(1.1);
		doc.line(0,pgHeight / 2,pgWidth,pgHeight / 2);
		// doc.setLineWidth();
		doc.setLineDash();

		// doc.setPage(i + 1);
		doc.text("Zum aufbewahren",pgWidth/2,pgHeight / 2 + 15, { align: "center" });

		doc.autoTable({
			startY: (3 * (pgHeight / 4)) - 5,
			margin: {top: 0, right: 0, bottom: 0, left: pgWidth/2 - 22} ,
			tableWidth: 44,
			theme: 'grid',
			headStyles: { fillColor: "#f3f4f4", textColor: 20},
			styles: { halign: "center"},
			showFoot: "never",
			head: [['CoronaID','PIN']],
			body: [[data[i][0],data[i][1]]],
		});
		
		
	}
	doc.save("CoroaIds.pdf");
}

function genPDF(data,papersize) {
	let lg=data.length;
	let body=[];
	for(let i=0; i < lg; i++) {
		
		body.push([data[i][0],data[i][0] + ' :: ' + data[i][1]]);
	}
	
	var doc = new jsPDF({unit: "mm", format: papersize});
	
	doc.autoTable({
		theme: 'grid',
		headStyles: { fillColor: "#f3f4f4", textColor: 20},
		bodyStyles: { 
			cellPadding: {
				left: 10,
				right: 0,
				top: 8,
				bottom: 8
			}
		},
		head: [['CoronaID f. Aktion', 'CoronaID + PIN f. Home']],
		body: body,
	});

	doc.save("CoroaIds.pdf");
	
}

function genCSV(JSONData,fileName,ShowLabel) {
    var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;
    var CSV = '';
    if (ShowLabel) {
        var row = "";
        for (var index in arrData[0]) {
            row += index + ',';
        }
        row = row.slice(0, -1);
        CSV += row + '\r\n';
    }
    for (var i = 0; i < arrData.length; i++) {
        var row = "";
        for (var index in arrData[i]) {
            var arrValue = arrData[i][index] == null ? "" : '"' + arrData[i][index] + '"';
            row += arrValue + ',';
        }
        row=row.slice(0, -1);
        CSV += row + '\r\n';
    }
    if (CSV == '') {
        console.error("Invalid data");
        return;
    }
    var fileName = "Result";
    if(msieversion()){
        var IEwindow = window.open();
        IEwindow.document.write('sep=,\r\n' + CSV);
        IEwindow.document.close();
        IEwindow.document.execCommand('SaveAs', true, fileName + ".csv");
        IEwindow.close();
    } else {
        var uri = 'data:application/csv;charset=utf-8,' + escape(CSV);
        var link = document.createElement("a");
        link.href = uri;
        link.style = "visibility:hidden";
        link.download = fileName + ".csv";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function msieversion() {
  var ua = window.navigator.userAgent;
  var msie = ua.indexOf("MSIE ");
  if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) // If Internet Explorer, return true
  {
    return true;
  } else { // If another browser,
  return false;
  }
  return false;
}	
	
})(jQuery);	
	