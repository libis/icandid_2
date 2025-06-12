<template>
  <div>
    <div v-if="activeResult != undefined">
      <div v-for="(field,idx) in fields" :key="idx">
          <div style="margin-bottom:.5em" v-if="showfield(idx)">
            <div class="has-text-weight-semibold" >{{ field }} <button style="border:0px;background-color:transparent;font-size:16px" v-if="idx=='sameAs'" v-tooltip="info"><i class="fa fa-info"></i></button></div>
            <div v-if="idx != '@id' && idx != 'associatedMedia'" v-html="show(idx)" style="word-wrap:break-word" v-linkified></div>
            <div v-else v-html="show(idx)" style="word-wrap:break-word"></div>
          </div>
      </div>
    </div>
    
  </div>
</template>
<script>

import { mapGetters } from "vuex";

export default {
  data() {
    return {
      fields: {
        "@type": this.$ml.get('type'),
        "@id":  this.$ml.get('identifier'),
        identifier: this.$ml.get('originalidentifier'),
        legislationType: this.$ml.get('legislationType'),
        publisher: this.$ml.get('publisher'),
        "_aggregator": this.$ml.get('aggregator'),
        printEdition: this.$ml.get('edition'),
        articleSection: this.$ml.get('articlesection'),
        pagination: this.$ml.get('pagination'),
        locationCreated: this.$ml.get('locationCreated'),
        datePublished:  this.$ml.get('publicationdate'),
        dateline: this.$ml.get('dateline'),
        headline: this.$ml.get('title'),
        name: this.$ml.get('title'),
        alternateName: this.$ml.get('alternateName'),
        genre:this.$ml.get('genre'),
        creator: this.$ml.get('creator'),
        author: this.$ml.get('author'),
        director: this.$ml.get('director'),
        producer: this.$ml.get('producer'),
        productionCompany: this.$ml.get('productionCompany'),
        actor:this.$ml.get('actor'),
        editor:this.$ml.get('editor'),
        contributor:this.$ml.get('contributor'),
        musicBy:this.$ml.get('musicBy'),
        sender:  this.$ml.get('sender'),
        recipient:  this.$ml.get('recipient'),
        legislationPassedBy: this.$ml.get('legislationPassedBy'),
        legislationResponsible: this.$ml.get('legislationResponsible'),
        description: this.$ml.get('description'),
        articleBody:  this.$ml.get('text'),
        text:  this.$ml.get('text'),
        keywords: this.$ml.get('keywords'),
        color: this.$ml.get('color'),
        isPartOf:this.$ml.get('collection'),
        mentions: this.$ml.get('mentions'),
        duration: this.$ml.get('duration'),
//        retweeted_tweet: this.$ml.get('retweeted_tweet'),
//        replied_to_tweet: this.$ml.get('replied_to_tweet'),
//        quoted_tweet: this.$ml.get('quoted_tweet'),
        contentUrl: this.$ml.get('content'),
        about: this.$ml.get('about'),
        inLanguage:this.$ml.get('language'),
        contentLocation:this.$ml.get('location'),
        associatedMedia:this.$ml.get('media'),
        trailer:this.$ml.get('trailer'),
        review:this.$ml.get('review'),
        sameAs: this.$ml.get('link'),
        url: this.$ml.get('permalink'),
        provider: this.$ml.get('provider'),
        license: this.$ml.get('license'),
        copyrightNotice:this.$ml.get('copyright'),
        sdDatePublished: this.$ml.get('sdDatePublished'),
        updatetime:this.$ml.get('updatetime'),
        
      },
      menuitems: [{ label: "Save item in set", name: "save" }],
      info: {
        content:this.$ml.get('dbinfotooltip'),
        html:true,
        trigger:'hover',
        autoHide:false,
      },
      colorNames:[]
    };
  },
  props:['activeResult','currentLang','highlights'],
  methods: {
    nl2br(strvar) {
      return strvar.replace(/\n/g, "<br />");
    },
    format(str,idx) {
      str = String(str)
      if (idx === 'duration') {
        let duration = this.$moment.duration(str);
        let d = "";
        if (duration._data.hours > 0) {
          d = d + duration._data.hours.toString().padStart(2, "0") + ":";
        }
        d = d + duration._data.minutes.toString().padStart(2, "0") + ":" + duration._data.seconds.toString().padStart(2, "0");
        str = d;
      }
      if (idx != "identifier") {
        if (this.getElasticQuery.highlight != undefined) {
          var search1 = this.getElasticQuery.highlight.pre_tags[0]
          var search2 = this.getElasticQuery.highlight.post_tags[0]
          var replace1 = search1.replace("<","[").replace(">","]")
          var replace2 = search2.replace("<","[").replace(">","]")

          str = str.replaceAll(search1,replace1)
          str = str.replaceAll(search2,replace2)
        }

        str = str.replaceAll("<","&lt;")
        str = str.replaceAll(">","&gt;")

        if (this.getElasticQuery.highlight != undefined) {
          str = str.replaceAll(replace1,search1)
          str = str.replaceAll(replace2,search2)
        }
      }

      let out = "";

      const URLMatcher = /^(?:(?:https?):\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,}))\.?)(?::\d{2,5})?(?:[/?#]\S*)?$/igm
      if (idx == 'url') {
        out = str.replace(URLMatcher, match => `<a href="${match}">${match}</a>`);
      } else {
        out = str.replace(URLMatcher, match => `<a href="${match}" target="_blank">${match}</a>`);
      }

      if (idx == 'color') {
        out  = '<div class="color-container"><div title="' + str + '" class="color-box" style="background-color:' + str + '"></div><div>' + this.$func.getColorName(str) + '</div></div>'
      }
      return out;
    },
    show(idx) {
      var dat = "";
      var out = "";
      if (this.activeResult != undefined) {
        if (this.activeResult._source[idx] != undefined) {
          dat = this.activeResult._source[idx]
        }
        if (typeof dat == 'string') {
          out = this.format(dat, idx);
          if (idx == '@type') {
            if (this.activeResult._source['additionalType'] != undefined && this.activeResult._source['additionalType'] != '') {
              out += ", " + this.activeResult._source['additionalType'];
            }
          }
          if (idx == "sdDatePublished" || idx == "updatetime") {
            var pubdat = new Date(dat)
            out = pubdat.toUTCString()
          }        
          return out
        }
        if (typeof dat == 'object') {
            if (Array.isArray(dat)) {

            for (var i in dat) {
              if (typeof dat[i] == 'string') {
                out += "<div>" + this.format(dat[i],idx) + "</div>"
              } else {
                var downloadFormat = "data:"+dat[i].encodingFormat + ";base64"
                if (dat[i]["@value"] != undefined) {
                  out += "<div>" + this.format(dat[i]["@value"],idx) + "</div>"
                } else {
                  if (dat[i].name != undefined) {
                    if ((idx == "legislationPassedBy" || idx == "legislationResponsible") && dat[i].memberOf != undefined && dat[i].memberOf.name != undefined) {
                      out += "<div>" + this.format(dat[i].name,idx) + " (" + dat[i].memberOf.name + ")</div>"
                    } else if(idx == "isPartOf") {
                      if (dat[i]["@type"] == "Collection") {
                        if (dat[i]["@id"] != undefined) {
                          out += '<div><a href="' + dat[i]["@id"] + '" target="_blank">' + dat[i].name["@value"] + '</a></div>';
                        } else {
                          out += '<div>' + dat[i].name["@value"] + '</div>';
                        }
                      }
                    } else if(idx == "identifier")  {
                      /*if (dat[i]['@id'] == "original_provider_id") {
                        out += "<div>" + this.format(dat[i].value,idx) + "</div>"
                      }*/
                      if (dat[i].name != undefined) {
                        out += "<div>" + this.format(dat[i].name,idx) + "&nbsp:&nbsp;" + this.format(dat[i].value,idx) + "</div>"
                      } else {
                        out += "<div>" + this.format(dat[i].value,idx) + "</div>"
                      }
                    } else {
                        if (dat[i].contentUrl == undefined || dat[i].contentUrl.substring(0,downloadFormat.length) != downloadFormat) {
                          if (dat[i]["url"] == undefined || dat[i]["url"] == null || dat[i]["url"] == "") {

                            if (dat[i]["sameAs"] == undefined || dat[i]["sameAs"] == null || dat[i]["sameAs"] == "") {
                              out += "<div>" + this.format(dat[i].name,idx) + "</div>"
                            } else {
                              out += "<div><a href='" + dat[i]["sameAs"] + "' target='_blank'>" + this.format(dat[i].name,idx) + "</a></div>"  
                            }
                          } else {
                            out += "<div><a href='" + dat[i]["url"] + "' target='_blank'>" + this.format(dat[i].name,idx) + "</a></div>"
                          }
                        }
                      }
                  }
                }
                if (dat[i]["@type"] == "MediaObject" || dat[i]["@type"] == "ImageObject") {
                  if (dat[i].contentUrl != undefined && dat[i].contentUrl.substring(0,downloadFormat.length) == downloadFormat) {
                    out += "<a download=\"" + dat[i].name + "\" href=\"" + dat[i].contentUrl+ "\">" + dat[i].name + "</a><br />"
                  } else {
                    if (dat[i].url != undefined) {
                      out += '<a target="_blank" href="' + dat[i].url + '">' + dat[i].url + '</a><br/>'
                    }
                    if (dat[i].contentUrl != undefined) {
                      out += '<a target="_blank" href="' + dat[i].contentUrl + '">' + dat[i].contentUrl + '</a><br/>'
                    }
                    if (dat[i].thumbnailUrl != undefined) {
                      out += '<a target="_blank" href="' + dat[i].thumbnailUrl + '">' + dat[i].thumbnailUrl + '</a><br/>'
                    }
/*                    if (dat[i].width != undefined && dat[i].height != undefined) {
                      out += dat[i].width + 'x' + dat[i].height + '<br/>'
                    }
                    if (dat[i].encodingFormat != undefined) {
                      out += dat[i].encodingFormat + '<br/>'
                    } */
                  }
                }

                if (dat[i]['@type'] == "PerformanceRole") {
                  if (dat[i]["actor"]["url"] == undefined || dat[i]["actor"]["url"] == null || dat[i]["actor"]["url"] == "") {
                    out += "<div>" + dat[i]['actor']['name']
                  } else {
                    out += "<div><a href='" + dat[i]["actor"]["url"] + "' target='_blank'>" + dat[i]['actor']['name'] + "</a>"
                  }
                  if (dat[i]["characterName"] != undefined && dat[i]["characterName"] != null && dat[i]["characterName"] != "") out += "&nbsp;(" + dat[i]["characterName"] + ")"
                  out += "</div>"
                }

                if(dat[i]["@type"] == "Review") {
                  out += "<div>"
                  if (dat[i]["author"]["name"] != undefined && dat[i]["author"]["name"] != null && dat[i]["author"]["name"] != "") {
                    if (dat[i]["author"]["url"] != undefined && dat[i]["author"]["url"] != null && dat[i]["author"]["url"] != "") {
                      out += "<div><a href='" + dat[i]["author"]["url"] + "' target='_blank'>" + dat[i]["author"]["name"] + "</a></div>"
                    } else {
                      out += "<div>" + dat[i]["author"]["name"] + "</div>"
                    }
                  }
                  if (dat[i]["dateCreated"] != undefined && dat[i]["dateCreated"] != null && dat[i]["dateCreated"]) {
                    out += "<div>"+dat[i]["dateCreated"]+"</div>"
                  }
                  if (dat[i]["reviewBody"]["@value"] != undefined && dat[i]["reviewBody"]["@value"] != null && dat[i]["reviewBody"]["@value"] != "") {
                    out += "<div>" + dat[i]["reviewBody"]["@value"] + "</div>"
                  }

                  out += "</div><br/>"
                }
                if(dat[i]["@type"] == "VideoObject") {
                  if(dat[i]["description"] != undefined && dat[i]["description"] != null && dat[i]["description"] != "") {
                    out += "<div>" + dat[i]["description"] + "</div>"
                  }
                }     

              }
            }
          } else {
            if (dat.name != undefined) {
              if ((idx == "legislationPassedBy" || idx == "legislationResponsible") && dat.memberOf != undefined && dat.memberOf.name != undefined) {
                out += this.format(dat.name,idx) + " (" + dat.memberOf.name + ")"
              } else if(idx == "isPartOf") {
                if (dat["@type"] == "Collection") {
                  if (dat["@id"] != undefined) {
                    out += '<div><a href="' + dat["@id"] + '" target="_blank">' + dat.name["@value"] + '</a></div>';
                  } else {
                    out += '<div>' + dat.name["@value"] + '</div>';
                  }
                }
              } else if(idx == "identifier")  {
                /*if (dat['@id'] == "original_provider_id") {
                  out += "<div>" + this.format(dat.value,idx) + "</div>"
                }*/
                if (dat.name != undefined) {
                  out += "<div>" + this.format(dat.name,idx) + "&nbsp:&nbsp;" + this.format(dat.value,idx) + "</div>"
                } else {
                  out += "<div>" + this.format(dat.value,idx) + "</div>"
                }
              } else {
                if (dat["url"] == undefined || dat["url"] == null || dat["url"] == "") {
                  if (dat["sameAs"] == undefined || dat["sameAs"] == null || dat["sameAs"] == "") {
                    out += "<div>" + this.format(dat.name,idx) + "</div>"
                  } else {
                    out += "<div><a href='" + dat["sameAs"] + "' target='_blank'>" + this.format(dat.name,idx) + "</a></div>"  
                  }
                } else {
                  out += "<a href='" + dat["url"] + "' target='_blank'>" + this.format(dat.name,idx) + "</a>"
                }
              }
            }
            if (dat["@value"] != undefined) {
              out = this.format(dat["@value"], idx)
            }
          }

          if (idx == 'sender' || idx == 'recipient') {
            out = out.replace("<div>","").replace("</div>","");
            if (this.activeResult != undefined) {
              if (this.activeResult._source[idx] != undefined) {
                var dat2 = this.activeResult._source[idx]
                if (dat2.alternateName != undefined) {
                  if (dat2.sameAs != undefined) {
                    out += "&nbsp;<a target=\"_blank\" href=\"" + dat2.sameAs + "\">@" + dat2.alternateName + "</a>"
                    
                  } else {
                    out += "&nbsp;@" + dat2.alternateName
                  }
                }
              }

            }
            out = "<div>" + out + "</div>"
          }

          if (idx == 'retweeted_tweet' || idx == 'replied_to_tweet' || idx == 'quoted_tweet') {
            out = "<a href='" + dat["url"] + "' target='_blank'>" + dat["value"] + "</a>";
          }

          if (dat['@type'] == "Place" && dat["geo"] != undefined) {
            for (const g of dat["geo"]) {
              if (g["@type"] == "GeoCoordinates") {
                out += ' <a style="font-size:10px" target="_blank" href="https://geohack.toolforge.org/geohack.php?pagename='+encodeURI(out)+'&params=' + g.longitude + ';' + g.latitude + '">' + g.longitude.toFixed(2) + ',' + g.latitude.toFixed(2) + '</a>';
              }
            }
          }

          if (dat['@type'] == "MediaObject" || dat['@type'] == "ImageObject") {
            if (dat.url != undefined) {
              out += '<a target="_blank" href="' + dat.url + '">' + dat.url + '</a><br/>'
            }
            if (dat.contentUrl != undefined) {
              out += '<a target="_blank" href="' + dat.contentUrl + '">' + dat.contentUrl + '</a><br/>'
            }
            if (dat.thumbnailUrl != undefined) {
              out += '<a target="_blank" href="' + dat.thumbnailUrl + '">' + dat.thumbnailUrl + '</a><br/>'
            }
/*            if (dat.width != undefined && dat.height != undefined) {
              out += dat.width + 'x' + dat.height + '<br/>'
            }
            if (dat.encodingFormat != undefined) {
              out += dat.encodingFormat + '<br/>'
            }*/
            
          }

          if (dat['@type'] == "PerformanceRole") {
            if (dat["actor"]["url"] == undefined || dat["actor"]["url"] == null || dat["actor"]["url"] == "") {
              out += "<div>" + dat['actor']['name']
            } else {
              out += "<div><a href='" + dat["actor"]["url"] + "' target='_blank'>" + dat['actor']['name'] + "</a>"
            }
            if (dat["characterName"] != undefined && dat["characterName"] != null && dat["characterName"] != "")  out += "&nbsp;(" + dat["characterName"] + ")"
            out += "</div>"
          }

          if(dat["@type"] == "Review") {
            out += "<div>"
            if (dat["author"]["name"] != undefined && dat["author"]["name"] != null && dat["author"]["name"] != "") {
              if (dat["author"]["url"] != undefined && dat["author"]["url"] != null && dat["author"]["url"] != "") {
                out += "<div><a href='" + dat["author"]["url"] + "' target='_blank'>" + dat["author"]["name"] + "</a></div>"
              } else {
                out += "<div>" + dat["author"]["name"] + "</div>"
              }
            }
            if (dat["dateCreated"] != undefined && dat["dateCreated"] != null && dat["dateCreated"]) {
              out += "<div>"+dat["dateCreated"]+"</div>"
            }

            if (dat["name"] != undefined && dat["name"] != null && dat["name"]) {
              if (dat["sameAs"] != undefined  && dat["sameAs"] != null && dat["sameAs"]) {
                out += "<div><a href='"+dat["sameAs"]+"' target='_blank'>"+dat["name"]+"</a></div>"
              } else {
                out += "<div>"+dat["name"]+"</div>"
              }
            }

            if (dat["reviewBody"]["@value"] != undefined && dat["reviewBody"]["@value"] != null && dat["reviewBody"]["@value"] != "") {
              out += "<div>" + dat["reviewBody"]["@value"] + "</div>"
            }

            out += "</div><br/>"
          }

          if(dat["@type"] == "VideoObject") {
            if(dat["description"] != undefined && dat["description"] != null && dat["description"] != "") {
              out += "<div>" + dat["description"] + "</div>"
            }
          }


          return out
        } else {
          return ""
        }
      }

      return idx + " : " + typeof dat;
    },
    showfield(idx) {
      if (this.activeResult._source[idx] != undefined) {
/*        if(idx == 'identifier') {
          if (this.activeResult._source[idx]['@id'] != 'original_provider_id') {
            return false
          }
        }*/
        return true
      }
      return false
    },
    setExtraTweets() {
      if (this.activeResult != undefined) {
        if (this.activeResult._source["identifier"] != undefined) {
          for(var elidx in this.activeResult._source["identifier"]) {
            var element = this.activeResult._source["identifier"][elidx];
            if (element["name"] == "retweeted_tweet_id") {
              this.activeResult._source["retweeted_tweet"] = element;
            }
            if (element["name"] == "quoted_tweet_id") {
              this.activeResult._source["quoted_tweet"] = element;
            }
            if (element["name"] == "replied_to_tweet_id") {
              this.activeResult._source["replied_to_tweet"] = element;
            }
          }
        }
      }
    },
    highlighter() {
      for (const i in this.activeResult.highlight) {
          for (const j in this.activeResult.highlight[i]) {
              this.changeByIndex(this.activeResult._source,i.split("."),this.activeResult.highlight[i][j])
          }
      }
    },
    isset(v) {
      return (v != undefined && v != null && v != "")
    },
    stripHTML(v) {
      if (typeof v != 'string') return v
      return v.replace(/(<([^>]+)>)/gi, "").replace(/[^0-9a-zA-Z]/gi, '');
    },
    changeByIndex(rec , idx, to) {
        if (this.isset(rec[idx[0]])){
            if (idx.length > 1) {
                if (typeof rec[idx[0]] === 'object') {
                    if (rec[idx[0]] instanceof Array){
                        for (let i = 0; i < rec[idx[0]].length; i++) {
                            this.changeByIndex(rec[idx[0]][i],idx.slice(1),to)

                        }
                    } else {
                        this.changeByIndex(rec[idx[0]],idx.slice(1),to)
                    }
                } else {
                    if (this.stripHTML(rec[idx[0]]) == this.stripHTML(to)) {
                        rec[idx[0]] = to
                    }
                }
            } else {
                if (this.stripHTML(rec[idx[0]]) == this.stripHTML(to)) {
                    rec[idx[0]] = to
                }
            }
        }
    }
  },
  watch : {
    activeResult : function() {
      this.setExtraTweets()
      if (this.activeResult != undefined) {
        if (this.highlights) {
          this.highlighter()
        }
        if (this.activeResult._source.name != undefined && this.activeResult._source.headline != undefined) {
          delete this.activeResult._source.headline
        }
      }
    },
    currentLang: function(){
      this.fields["@type"]= this.$ml.get('type')
      this.fields["@id"]= this.$ml.get('identifier')
      this.fields.identifier = this.$ml.get('originalidentifier')
      this.fields.publisher= this.$ml.get('publisher')
      this.fields["_aggregator"] = this.$ml.get('aggregator')
      this.fields.printEdition= this.$ml.get('edition')
      this.fields.articleSection= this.$ml.get('articlesection')
      this.fields.pagination= this.$ml.get('pagination'),
      this.fields.datePublished=  this.$ml.get('publicationdate')
      this.fields.dateline= this.$ml.get('dateline')
      this.fields.headline= this.$ml.get('title')
      this.fields.name= this.$ml.get('title')
      this.fields.alternateName= this.$ml.get('alternateName')
      this.fields.genre= this.$ml.get('genre')
      this.fields.creator= this.$ml.get('creator')
      this.fields.author= this.$ml.get('author')
      this.fields.director= this.$ml.get('director')
      this.fields.producer= this.$ml.get('producer')
      this.fields.productionCompany= this.$ml.get('productionCompany')
      this.fields.actor= this.$ml.get('actor')
      this.fields.editor=this.$ml.get('editor')
      this.fields.contributor=this.$ml.get('contributor')
      this.fields.musicBy=this.$ml.get('musicBy')
      this.fields.sender=  this.$ml.get('sender')
      this.fields.recipient=  this.$ml.get('recipient')
      this.fields.description= this.$ml.get('description')
      this.fields.articleBody=  this.$ml.get('text')
      this.fields.text=  this.$ml.get('text')
      this.fields.keywords= this.$ml.get('keywords')
      this.fields.color= this.$ml.get('color')
      this.fields.isPartOf = this.$ml.get('collection')
      this.fields.mentions= this.$ml.get('mentions')
      this.fields.duration= this.$ml.get('duration')
//        this.fields.retweeted_tweet= this.$ml.get('retweeted_tweet')
//        this.fields.replied_to_tweet= this.$ml.get('replied_to_tweet')
//        this.fields.quoted_tweet= this.$ml.get('quoted_tweet')
      this.fields.contentUrl= this.$ml.get('content')
      this.fields.about= this.$ml.get('about')
      this.fields.inLanguage= this.$ml.get('language')
      this.fields.contentLocation= this.$ml.get('location')
      this.fields.associatedMedia= this.$ml.get('media')
      this.fields.trailer= this.$ml.get('trailer')
      this.fields.review= this.$ml.get('review'),
      this.fields.sameAs= this.$ml.get('link')
      this.fields.url= this.$ml.get('permalink')
      this.fields.provider= this.$ml.get('provider')
      this.fields.sdDatePublished= this.$ml.get('sdDatePublished')
      this.info.content = this.$ml.get('dbinfotooltip')
    }
  },
  created() {
    this.setExtraTweets();
    
  },
  computed: {
    ...mapGetters(["getElasticQuery"]),
  }
};
</script>
<style>
.highlighted {
/*  text-decoration:underline; */
  background-color: Yellow;
  padding-left:3px;
  padding-right:3px

}
.color-container {
	display:flex;
}
.color-box {
	border:1px solid Black;
	width:10px;
	height:10px;
	margin-right:3px;
	margin-top:6px;
}
</style>