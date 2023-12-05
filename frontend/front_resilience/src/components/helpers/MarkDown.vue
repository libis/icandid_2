<template>
    <div id="markdown" v-html="myMarkDown"></div>
</template>
<script>
import { marked } from 'marked'
const renderer = {
  heading(text, level) {
    return `
            <h${level} class="title is-${level}" style="margin-top:10px;color: #757763;">
              ${text}
            </h${level}>`;
  },
  link(href, title, text) {
    if (title == null) {
        return `<a href="${href}" target="_blank">${text}</a>`
    } else {
        return `<a href="${href}" title="${title}" target="_blank">${text}</a>`
    }
  },
  paragraph(text) {
    return `<p>${text}</p>`
  }
  
};

marked.use({ renderer });

export default {
    props:['markdown'],
    data() {
        return {
    
        }
    },
    computed:{
        myMarkDown() {
            if (this.markdown == null) {
                return ""
            }

            //var tmp = marked(this.markdown.replace(/\n/g, "<br />\n"))
            var tmp = marked(this.markdown)

            var re = /\{([a-z-]+)\}/ig
            var z = null
            var n = ""

            while(null != (z=re.exec(tmp))){
                n = "<i aria-hidden=\"true\" class=\"fa fa-" + z[1] + "\"></i>"
                tmp = tmp.replace(z[0],n)
            } 

            return tmp
        }
    },
    watch: {
        markdown : function() {
            this.$forceUpdate();
        }
    }
}
</script>
<style>
#markdown ul li:before {
    content:"- ";
    text-indent:-5px;
}
#markdown li {
    padding-top:5px;
    padding-bottom:5px
}
#markdown ul {
    margin-left:50px;
    margin-bottom:20px
}
#markdown ol {
    margin-top:-15px;
    margin-bottom:20px;
    margin-left:67px
}
#markdown img {
    border: 1px solid Black;
    padding:12px
}
#markdown strong {
    color:#757763
}
#markdown p {
    padding-bottom:20px
}
</style>