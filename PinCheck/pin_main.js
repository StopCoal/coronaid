/////////////////////////////////////////////////////////////////////////////
//
(function($){
	
	$(document).ready(function()
	{

		$('.input_char').autotab({ 
				maxlength: 1,
				uppercase: true,
				format: "custom",
				pattern: "[^ABCDEFGHIKLMNOPSTUWXYZabcdefghiklmnopstuwxyz]"
			
		});
				
		$('#search_pin_text').autotab({ 
				maxlength: 4,
				uppercase: true,
				format: "numeric",
				pattern: "[^ABCDEFGHIKLMNOPSTUWXYZabcdefghiklmnopstuwxyz]"
			
		});

		
		$(".search_char_buttons button").click(function(e) {
			if(!$(this).hasClass("active") && $(".search_char_buttons button.active").length >=3 ){
				e.preventDefault();
				alert("Es können nicht mehr als drei Wildcards benutzt werden");
				return false;
			}
			
			let srchText=$("#search_id_text").val();
			
			$(this).toggleClass("active");
			if($(this).hasClass("active")) {
				$($(this).data("target")).addClass("wildcard");				
			}
			else {
				$($(this).data("target")).removeClass("wildcard");				
			}
			
			doSearchId();
		});
		
		$("#search_btn").click(doSearchId);
		
		$(".search_results").click(function(e){
			let id=$(e.target).text();
			let pin=$(e.target).closest(".result_item").find(".found_pin").text();
			$("#form_corona_id").val(id);
			$("#form_pin").val(pin);
		});

				
		$(".modal.modal_dlg").on('show.bs.modal', function (e) {
			let jTitle=$(this).find(".modal-title");
			let jBody=$(this).find(".modal-body");
			loadHelp(jTitle,jBody);
		});
		
		if($(".timeout_alert").length > 0) {
			setTimeout(function(){
				$(".timeout_alert").remove();
			},5000);
		}
		// setTimeout(refreshStationData,1000);
	});
	
	
	function getSrchId(){
		return $(".input_char.char1").val() + $(".input_char.char2").val() + $(".input_char.char3").val() +
				$(".input_char.char4").val() + $(".input_char.char5").val();
	}
	
	function doSearchId() {
		let pin=$("#search_pin_text").val();
		// let srchText=$("#search_id_text").val();
		let srchText=getSrchId();

		
		if(pin.length == 0 && srchText.length == 0) {alert("ID und/oder PIN eingeben"); return; }
		if(pin.length > 0 && pin.length != 4) {alert("PIN muss genau 4 Stellen haben"); return; }
		if(srchText.length > 0 && srchText.length != 5) {alert("ID muss genau 5 Stellen haben"); return; }
		
		let arText=srchText.split("");
		
		if($("#char1").hasClass("active")) arText[0]='_';
		if($("#char2").hasClass("active")) arText[1]='_';
		if($("#char3").hasClass("active")) arText[2]='_';
		if($("#char4").hasClass("active")) arText[3]='_';
		if($("#char5").hasClass("active")) arText[4]='_';
		
		srchText=arText.join("");
		$("#search_id_text").val(srchText)
		searchById(srchText,pin);
	}
			
	function searchById(corona_id,pin) {
		
		$('.modal.busy_modal').modal({
		  keyboard: false,
		  show: true
		})
		
		
		$.post({
			url: "",
			data: {
				action: "search_by_id",
				format: "json",
				corona_id: corona_id,
				pin: pin
			},
			success: function(data,  textStatus,  jqXHR ) {
				
				
				if(!data.status) {
					alert(data.msg);
					return;
				}
				let iLines=data.data.length;
				let content='';
				for (let i=0; i < iLines;i++) {
					content += "<div class='result_item'><button class='link found_corona_id'>" + data.data[i].corona_key  + "</button>-";
					content += "<span class='found_pin'>" + data.data[i].pin + "</span></div>";
				}
				
				// content += '</tbody></table>';
				$(".search_results").html(content);
				
			}
		}).done(function(){
			$('.modal.busy_modal').modal('hide');
		});				
		
	}
	
})(jQuery);

