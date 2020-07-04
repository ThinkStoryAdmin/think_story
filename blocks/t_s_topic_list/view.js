$(function() {
	$('select[data-select=topic-select]').change(function() {
        var action = $(this).attr('data-action').replace('--topic--', $(this).val());
        console.log($(this));
        console.log("Link: " + window.location.href);
        console.log("Action: " + action);
        let parts = action.split('/');
        console.log(parts)
        //window.location.href = action;
    });

    $(document).change(function(event){
        console.log(event)
        console.log(event.target)

        console.log(event.target.id)

        if(event.target.id.includes('think-story-drop-down2-')){
            console.log("V2")
            var ids = event.target.id.split('-')
            var blockID = ids[ids.length - 1]; 
            var topicID = event.target.value;
            console.log(topicID)
            console.log(blockID)
            console.log(window.location)

            let current_location = window.location.href
            let new_location = ''
            if (current_location.indexOf('tstbID') == -1) {
                new_location = current_location
                if(current_location.indexOf('/topic2') == -1){
                    new_location += '/topic2/'
                } else if(current_location.indexOf('/topic2/') == -1){
                    new_location += '/topic2/'
                }
                new_location += 'tstbID'+blockID+'/'+topicID;
            } else {
                let index = current_location.pathname.indexOf('tstbID'+blockID+'/')
            }

            //let url = window.location.indexOf('/topic')
            console.log(new_location)
            //window.location.href = new_location
        }
    });   
});