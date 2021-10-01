const learnpath = {

    data: [],
    slugs: [],
    config: [],
    info_box_offset: 0,
    vw:0,
    is_mobile:false,

    adjust_labels: function () {
        let data = learnpath.data;
        let tx, ty, g;

        for (const tnode of data) {
            tx = tnode.cx - tnode.r / 3;
            ty = tnode.cy;
            text = document.getElementById('text_' + tnode.slug);
            bbox = text.getBBox();
            text.setAttribute('x', tx - Math.floor(bbox.width / 2));
            text.setAttribute('y', ty + Math.floor(bbox.height));
        }
    },

    adjust_info_box: function () {


        let box = document.querySelector('svg');
        let height = box.scrollHeight;
        box = document.querySelector('.lp-infobox');
        box.style.top = (height - learnpath.info_box_offset)
        +'px';
    },

    check_answer: function (id) {

        var slug = learnpath.check_active_hashtag();
        var data = learnpath.data;
        var answer = document.getElementById('lp_answer_' + id).value;

        for (const node of data) {
            if (node.slug == slug) {



                console.log(node.code);


                if (!node.code || answer.toLowerCase().indexOf(node.code.toLowerCase())>-1) {
                    var hash = learnpath.get_next_slug(slug);

                    location.hash = hash;
                    document.getElementById('lp_answer_' + id).value = '👍 Richtig!';

                    sessionStorage.setItem('learnpath_hash_'+learnpath.post_id, hash);

                    document.getElementById('status_' + id).value = location.href;
                    document.getElementById('quest_form_status_' + id).style.display = "block";

                } else {
                    document.getElementById('lp_answer_' + id).value = '🤔 Probier eine andere Lösung.';
                }
                setTimeout(function () {
                    document.getElementById('lp_answer_' + id).value = '';
                }, 2000);
            }
        }

        return false;
    },
    copy_status: function (id) {

        /* Get the text field */
        var copyText = document.getElementById(id);
        copyText.style.display = 'block';

        /* Select the text field */
        if(learnpath.is_mobile){
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
        }else{
            copyText.select();
        }
        /* Copy the text inside the text field */
        navigator.clipboard.writeText(copyText.value);

        /* Alert the copied text */
        alert("Die URL zur aktuellen Wegmarke wurde in die Zwischenablage kopiert. Du kannst sie in dein Lerntagebuch kopieren oder per E-Mail deiner Lehrkraft schicken. ");
    },
    hide_quest: function () {
        document.querySelector('.lp-infobox').style.display = "none";
    },
    show_quest: function (slug) {
        let data = learnpath.data;

        for (const node of data) {
            if (node.slug == slug) {
                var infobox = document.querySelector('.lp-infobox');
                infobox.style.display = "block";
                let box_width = infobox.offsetWidth;

                document.querySelector('.lp-infobox').style.top = (document.getElementById(slug).getBBox().y + document.getElementById(slug).getBBox().height) + 'px';
                document.getElementById('quest_'+learnpath.id).innerHTML = node.description;
                document.getElementById('quest_title_'+learnpath.id).innerHTML = node.title;

                //learnpath.is_strict

                curr_slug = learnpath.check_active_hashtag();

                gpoint = document.getElementById(slug);

                gpoint.setAttribute('style', "display:unset;opacity:1");

                let circle = gpoint.getBBox();

                //berechne die position des Elements in relation zur relativen höhe in der SVG
                let realw = Math.floor(document.getElementById(learnpath.id).parentElement.offsetWidth);


                if (realw < box_width) {
                    infobox.style.width = realw + 'px!important';
                    box_width = realw;

                }

                let svg_left = Math.floor(document.getElementById(learnpath.id).parentElement.offsetLeft);
                let svg_right = svg_left + realw;


                let max_left = svg_right - box_width;


                let svgw = learnpath.vw;
                ;
                let factor = realw / svgw;
                let radius = (node.r * factor);

                let top = Math.floor(circle.y * factor) + (node.r * factor * 2) + 30;

                let left = Math.floor(circle.x * factor);

                //Infobox zentrieren
                //max_left = window.innerWidth-infobox.offsetWidth;
                left -= (box_width / 2) - radius;

                if (left < 0) {
                    left = 0;
                }
                if (left > max_left) {
                    left = max_left;
                }
                //left = (left > max_left)?max_left:left;

                infobox.style.top = top + 'px';
                infobox.style.left = left + 'px';

                window.scrollTo({
                    top: top,
                    behavior: 'smooth'
                });

                qform = document.getElementById('quest_form_'+learnpath.id);
                qform.style.display = 'none';


                let display_form = false;

                if(node.code){
                    display_form = true;
                }else if(learnpath.is_strict && !node.code){

                    console.log(learnpath.is_strict);

                    document.getElementById('quest_title_'+learnpath.id).innerHTML = 'Abgrund!';
                    document.getElementById('quest_'+learnpath.id).innerHTML = "Der Weg ist versperrt. "
                      + "Du kommst nicht weiter und musst dich auf eine Wartezeit einstellen. " +
                        "(Die nächsten Herausforderungen sind noch nicht frei geschaltet, aber schon bald ..!)<br>"+
                        "Wir sehen uns!<br><br><hr>";
                    document.getElementById('quest_form_status_'+learnpath.id).style.display='block';



                    display_form = false;

                }else if(!node.code){

                    document.getElementById('lp_answer_' + learnpath.id).value = 'Ohne Lösungswort ...';
                    display_form = true;

                }


                if (display_form && curr_slug == slug && typeof (this.get_next_slug(curr_slug)) != 'undefined') {
                    if (learnpath.get_next_slug(slug)) {
                        qform.style.display = 'block';
                    }
                }
            }

        }
    },

    get_node_from_slug: function(slug){

        for (const nodeElement of this.data) {
            if(nodeElement.slug == slug){
                return nodeElement;
            }
        }
        return false;
    },

    get_next_slug: function (curr_slug) {

        var slugs = learnpath.slugs;

        for (i = 0; i < slugs.length; i++) {
            if (slugs[i] == curr_slug) {
                var nextslug = i + 1;
                return slugs[nextslug]
            }
        }


    },

    check_visibles: function (curr_slug) {

        curr_slug = curr_slug || false;

        var slugs = learnpath.slugs;
        var curr_gpoint;

        if (curr_slug) {
            curr_gpoint = document.getElementById(curr_slug);
            if (!curr_gpoint) {
                curr_slug = false;
            }

        }
        var showslugs = 1;
        for (i = 0; i < slugs.length; i++) {
            if (slugs[i] == curr_slug) {
                showslugs = i + 1;
            }
        }

        for (i = 0; i < showslugs; i++) {
            gpoint = document.getElementById(slugs[i]);
            gpoint.setAttribute('style', "display:unset;opacity:0.5");
        }
        return slugs[showslugs - 1];

    },


    check_active_hashtag: function (e) {

        curr_slug = location.hash.substr(1);

        if (typeof (e) === 'undefined') {
            return learnpath.check_visibles(curr_slug);
        } else if (curr_slug == 'undefined') {

            sessionStorage.removeItem('learnpath_hash_'+learnpath.post_id);
        } else {
            learnpath.show_quest(curr_slug);
        }


    }


}
