window.doc_page = {
    addon: 'Low Link',
    title: 'Tags',
    sections: [
        {
            title: '',
            type: 'tagtoc',
            desc: 'Low Link has the following front-end tags: ',
        },
        {
            title: '',
            type: 'tags',
            desc: ''
        },
    ],
    tags: [

        {
            tag: '{exp:low_link:apply}',
            shortname: 'exp_low_link_apply',
            summary: "",
            desc: "",
            sections: [
                {
                    type: 'params',
                    title: 'Tag Parameters',
                    desc: '',
                    items: [
                        {
                            item: 'site',
                            desc: 'Which site(s) is/are used to look up the entries. Defaults to the current site.',
                            type: 'Site',
                            accepts: '',
                            default: 'Current',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_link:apply site="site"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'channel',
                            desc: 'Channel to filter by.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_link:apply channel="blog"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'field',
                            desc: '	Which field is used for matching. Defaults to title.',
                            type: '',
                            accepts: '',
                            default: 'title',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_link:apply field="title"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'tag',
                            desc: 'Which html tag is generated around the found entry. Defaults to a.',
                            type: 'Html tag type',
                            accepts: '',
                            default: '<a>',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_link:apply tag="b"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        {
                            item: 'tag:[attribute]',
                            desc: '	You can set any amount of tag attributes, for example: tag:href or tag:class, which are applied to the given tag. Inside these attributes, you can use two markers: %%entry_id%% and %%url_title%%, which are replaced by the matching entry.',
                            type: '',
                            accepts: '',
                            default: '',
                            required: false,
                            added: '',
                            examples: [
                                {
                                    tag_example: `{exp:low_link:apply tag:[href]="/thesaurus/%%url_title%%/"}`,
                                    outputs: ``
                                 }
                             ]
                        },
                        
                        
                      
                    ]
                }
            ]
        },

    ]
};