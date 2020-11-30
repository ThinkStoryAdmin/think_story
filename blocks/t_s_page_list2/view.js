//Some of the variables / functions are declared in view.php as they reference PHP
//Function that gets pages through AJAX (method in controller), and calls method to populate grid
function getPages(formdata){
    for(var i = 0; i < formdata.topics.length; i++){
        if(formdata.topics[i] == null || formdata.topics[i] == undefined || formdata.topics[i] == 'null'){
            formdata.topics[i] = -1
        }
        /*console.log("Value: " + i)
        console.log(formdata.topics[i])
        console.log("Type: " + typeof formdata.topics[i])*/
    }
    console.log("Sending topics: ")
    console.log(formdata)
    return $.ajax({
        type        : 'POST',
        dataType    : 'json',
        data        : formdata,
        url         : urlFilterAction,
        encode      : true,
        beforeSend: function(jqXHR, settings){  //WHY TF WOULD I COMMENT THIS OUT!!!
            jqXHR.url = settings.url;
            //jqXHR.dataTA = formdata;
            $("#loader").show();
            $('#tspages').empty();
        },
        success: function(response){},
        complete:function(data){
            setTimeout(() => {  $("#loader").hide(); }, 1000);
        },
        error:function(jqXHR, exception){
            console.log(exception);
			console.log(jqXHR.url);
			//console.log(jqXHR.dataTA);
			console.log(jqXHR.responseText);
            $("#tspages").empty().append(`
                <div>
                     
                </div>
            `)
        }
    });
}

//Function that fills the grid with the found pages
function fillPageGrid(response, paramsToAddToURL){
    console.log("Results")
    console.log(response.result)
    console.log("Status")
    console.log(response.status)
    //console.log("XHR")
    //console.log(response.xhr)
    console.log("Params to add to URL")
    console.log(paramsToAddToURL)

    var $pagegrid = $("#tspages").empty()
    var appendingPages = "";
    if(response.status == 'success'){
        //If pages are properly got, we can now replace them!!!
        var counter = 0;
        console.log("Number of pages" + response.result.pagedata.length)

        if(response.result.pagedata.length < 1){
            appendingPages += `
                <div>
                     
                </div>
            `
        } else {
            response.result.pagedata.forEach(element => {
                console.log("element")
                var urlGridItem = response.result.pagedata[counter].url 
                if(typeof paramsToAddToURL !== 'undefined'){
                    urlGridItem += '?' 
                    urlGridItem += paramsToAddToURL.toString()
                }
                var currPage = `
                <div id="page-item" class="ts-pl2-grid-item ts-pl2-grid-item-flex">
                    <div class="ts-pl2-header" style="background-color:${response.result.pagedata[counter].color}">
                        ${response.result.pagedata[counter].theme}
                    </div>
                    <div class="ts-pl2-details"><a
                        class="ts-pl2-details-content"
                        href="${urlGridItem}"
                        target="_self"
                    >
                        ${response.result.pagedata[counter].title}
                    </a></div>
                </div>
                `
                appendingPages += currPage
                counter += 1
            })
        }
    } else {
        appendingPages += `
            <div>
                Error getting pages!
            </div>
        `
    }
    $pagegrid.append(appendingPages)
}

//Sets the topic drop-down menus
function setSelects(values, callingBlock){
    var blocks = []
    var block = $(".topic_select_parent").closest('.topic_select_parent');
    
    blocks = block.find(':input')

    let updatedBlocks = []
    for(var y=0; y<values.length;y++){
        if(!(values[y] == -1)){
            for(var i = 0; i< blocks.length; i++){  //For all drop-downs, if it has the current searching value, then set it to said value
                if($("#"+blocks[i].id +" option[value='"+values[y]+"']").length > 0){
                    if(!(updatedBlocks.includes(blocks[i].id))){
                        if(!(values[y] == -1)){
                            updatedBlocks.push(blocks[i].id)
                        }
                        
                        $("#"+blocks[i].id).val(values[y].toString())
                    }
                }
            }
        } else {
            console.log("Value is first index, DO NOT SET")
        }
    }
    updatedBlocks = []
}

$(function() {
    console.log( "View.js ready!" );

    const urlParams = new URLSearchParams(window.location.search);
    let urlTopics = urlParams.getAll('topics[]');
    if(!urlTopics === undefined || !urlTopics.length == 0){     //Topics defined, request search    //TODO pretty sure this is an &&
        console.log("Topics defined!");
        setSelects(urlTopics);
        getPages({topics:urlTopics}).then(function(result,status,xhr){
            var pageresponse = {result,status,xhr}
            fillPageGrid(pageresponse, urlParams.toString())
        });
    } else {        //No topics defined, base search
        console.log("No topics defined!");
        getPages({topics:[]}).then(function(result,status,xhr){
            var pageresponse = {result,status,xhr}
            fillPageGrid(pageresponse, undefined)
        });
    }

    //On reload button click: Reset drop down & get list of the new resetted values
    $('#tsreload').click(function(){
        var blocks = $(this).closest('.topic_select_parent').find(':input')
        const urlParams = new URLSearchParams();
        for(var i = 0; i < blocks.length; i++){
            $("#"+blocks[i].id).val("-1");
            var val = $('#'+blocks[i].id).val()
            if((!(typeof val == 'null')) && (!(typeof val == 'undefined')) && (!(val == null))){
                urlParams.append('topics[]', val)
            }
        }

        if(sendToAnotherPage == 1){
            window.location.href = sendToAnotherPageIDURL + '?' + urlParams.toString()
        } else {
            getPages({topics:[-1,-1,-1]}).then(function(result,status,xhr){ //If we don't go to a new page, update the current page
                var pageresponse = {result,status,xhr}
                fillPageGrid(pageresponse, undefined)
            })
            window.history.pushState("object or string", "Page Title", window.location.href.split('?')[0] + '?' + urlParams.toString());
        }
    })

    //If any of the topic selects change, update the URL and request the pages
	$('.pagelist2[data-action=topic-select2]').change(function() {
        console.log("CHANGED TOPICS")
        var blocks = $(this).closest('.topic_select_parent').find(':input')
        const urlParams = new URLSearchParams();
        let values = []
        for(var i = 0; i < blocks.length; i++){
            var val = $('#'+blocks[i].id).val()
            values.push(val)
            if((!(typeof val == 'null')) && (!(typeof val == 'undefined')) && (!(val == null))){
                urlParams.append('topics[]', val)
            }
        }

        switch(iRedirectMethod){
            case 1:
                window.location.href = sendToAnotherPageIDURL + '?' + urlParams.toString()
                break;
            case 2:
                var url = new URL(window.location.href)
                var urlParams = new URLSearchParams(url.searchParams)
                window.location.href = window.location.href.split("/").slice(0, -1 * (numberUpRedirect)).join("/") + "?" + urlParams.toString();
                break;
            
            case 0:
            default:
                getPages({topics: values}).then(function(result,status,xhr){ //If we don't go to a new page, update the current page
                    var pageresponse = {result,status,xhr}
                    fillPageGrid(pageresponse, urlParams.toString())
                })
                window.history.pushState("object or string", "Page Title", window.location.href.split('?')[0] + '?' + urlParams.toString());
                break;
        }

        /*if(sendToAnotherPage == 1){
            window.location.href = sendToAnotherPageIDURL + '?' + urlParams.toString()
        } else {
            getPages({topics: values}).then(function(result,status,xhr){ //If we don't go to a new page, update the current page
                var pageresponse = {result,status,xhr}
                fillPageGrid(pageresponse, urlParams.toString())
            })
            window.history.pushState("object or string", "Page Title", window.location.href.split('?')[0] + '?' + urlParams.toString());
        }*/
    });
});