this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.action=this.WP_Optimize_Handlebars.action||{},this.WP_Optimize_Handlebars.action.handlebars=Handlebars.template({1:function(l,e,n,t,a){return'    <span class="wpo_edit_event"><span class="dashicons dashicons-edit"></span></span>\n'},compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s,o=null!=e?e:l.nullContext||{},u=l.escapeExpression,r=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'<div class="wpo_event_actions">\n'+(null!=(i=r(n,"if").call(o,null!=e?r(e,"stored"):e,{name:"if",hash:{},fn:l.program(1,a,0),inverse:l.noop,data:a,loc:{start:{line:2,column:4},end:{line:4,column:11}}}))?i:"")+'    <span class="wpo_remove_event" data-count="'+u((s=null!=(s=r(n,"count")||(null!=e?r(e,"count"):e))?s:l.hooks.helperMissing,"function"==typeof s?s.call(o,{name:"count",hash:{},data:a,loc:{start:{line:5,column:47},end:{line:5,column:56}}}):s))+'"><span class="dashicons dashicons-no-alt" title="'+u(l.lambda(null!=(i=null!=e?r(e,"wpoptimize"):e)?r(i,"remove_task"):i,e))+'"></span></span>\n</div>\n'},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.daily=this.WP_Optimize_Handlebars.daily||{},this.WP_Optimize_Handlebars.daily.handlebars=Handlebars.template({compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s=l.lambda,o=l.escapeExpression,u=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return"<label>"+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time"):i,e))+'\n<input type="time" class="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"class_name"):i,e))+'" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][time]" value="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time_value"):i,e))+'">\n</label>'},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.fortnightly=this.WP_Optimize_Handlebars.fortnightly||{},this.WP_Optimize_Handlebars.fortnightly.handlebars=Handlebars.template({1:function(l,e,n,t,a){var i,s=l.escapeExpression,o=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'    <option value="'+s((i=null!=(i=o(n,"key")||a&&o(a,"key"))?i:l.hooks.helperMissing,"function"==typeof i?i.call(null!=e?e:l.nullContext||{},{name:"key",hash:{},data:a,loc:{start:{line:6,column:19},end:{line:6,column:27}}}):i))+'">'+s(l.lambda(e,e))+"</option>\n"},compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s=l.lambda,o=l.escapeExpression,u=null!=e?e:l.nullContext||{},r=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return"<label>"+o(s(null!=(i=null!=e?r(e,"details"):e)?r(i,"time"):i,e))+'\n<input type="time" class="'+o(s(null!=(i=null!=e?r(e,"details"):e)?r(i,"class_name"):i,e))+'" name="wp-optimize-auto['+o(s(null!=(i=null!=e?r(e,"details"):e)?r(i,"count"):i,e))+'][time]" value="'+o(s(null!=(i=null!=e?r(e,"details"):e)?r(i,"time_value"):i,e))+'">\n</label>\n<select class="wpo_week_number" name="wp-optimize-auto['+o(s(null!=(i=null!=e?r(e,"details"):e)?r(i,"count"):i,e))+'][week]">\n'+(null!=(i=r(n,"each").call(u,null!=(i=null!=e?r(e,"details"):e)?r(i,"week"):i,{name:"each",hash:{},fn:l.program(1,a,0),inverse:l.noop,data:a,loc:{start:{line:5,column:4},end:{line:7,column:13}}}))?i:"")+'</select>\n<select class="wpo_week_days" name="wp-optimize-auto['+o(s(null!=(i=null!=e?r(e,"details"):e)?r(i,"count"):i,e))+'][day]">\n'+(null!=(i=r(n,"each").call(u,null!=(i=null!=e?r(e,"details"):e)?r(i,"week_days"):i,{name:"each",hash:{},fn:l.program(1,a,0),inverse:l.noop,data:a,loc:{start:{line:10,column:4},end:{line:12,column:13}}}))?i:"")+"</select>"},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.monthly=this.WP_Optimize_Handlebars.monthly||{},this.WP_Optimize_Handlebars.monthly.handlebars=Handlebars.template({1:function(l,e,n,t,a){var i,s=l.escapeExpression,o=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'    <option value="'+s((i=null!=(i=o(n,"key")||a&&o(a,"key"))?i:l.hooks.helperMissing,"function"==typeof i?i.call(null!=e?e:l.nullContext||{},{name:"key",hash:{},data:a,loc:{start:{line:7,column:19},end:{line:7,column:27}}}):i))+'">'+s(l.lambda(e,e))+"</option>\n"},compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s=l.lambda,o=l.escapeExpression,u=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return"<label>"+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time"):i,e))+'\n<input type="time" class="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"class_name"):i,e))+'" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][time]" value="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time_value"):i,e))+'">\n</label>\n<label>'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"day_number"):i,e))+'</label>\n<select class="wpo_day_number" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][day_number]">\n'+(null!=(i=u(n,"each").call(null!=e?e:l.nullContext||{},null!=(i=null!=e?u(e,"details"):e)?u(i,"days"):i,{name:"each",hash:{},fn:l.program(1,a,0),inverse:l.noop,data:a,loc:{start:{line:6,column:4},end:{line:8,column:13}}}))?i:"")+"</select>"},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.once=this.WP_Optimize_Handlebars.once||{},this.WP_Optimize_Handlebars.once.handlebars=Handlebars.template({compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s=l.lambda,o=l.escapeExpression,u=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return"<label>"+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"date"):i,e))+'\n<input type="date" class="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"class_name"):i,e))+'" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][date]" value="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"date_value"):i,e))+'" min="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"today"):i,e))+'">\n</label>\n<label>'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time"):i,e))+'\n<input type="time" class="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"class_name"):i,e))+'" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][time]" value="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time_value"):i,e))+'">\n</label>'},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.optimizations=this.WP_Optimize_Handlebars.optimizations||{},this.WP_Optimize_Handlebars.optimizations.handlebars=Handlebars.template({1:function(l,e,n,t,a){var i,s=null!=e?e:l.nullContext||{},o=l.hooks.helperMissing,u="function",r=l.escapeExpression,p=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'    <option value="'+r((i=null!=(i=p(n,"id")||(null!=e?p(e,"id"):e))?i:o,typeof i===u?i.call(s,{name:"id",hash:{},data:a,loc:{start:{line:3,column:19},end:{line:3,column:25}}}):i))+'" '+r((i=null!=(i=p(n,"selected")||(null!=e?p(e,"selected"):e))?i:o,typeof i===u?i.call(s,{name:"selected",hash:{},data:a,loc:{start:{line:3,column:27},end:{line:3,column:39}}}):i))+">"+r((i=null!=(i=p(n,"optimization")||(null!=e?p(e,"optimization"):e))?i:o,typeof i===u?i.call(s,{name:"optimization",hash:{},data:a,loc:{start:{line:3,column:40},end:{line:3,column:56}}}):i))+"</option>\n"},compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s,o=null!=e?e:l.nullContext||{},u=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'<select class="wpo_auto_optimizations" name="wp-optimize-auto['+l.escapeExpression((s=null!=(s=u(n,"count")||(null!=e?u(e,"count"):e))?s:l.hooks.helperMissing,"function"==typeof s?s.call(o,{name:"count",hash:{},data:a,loc:{start:{line:1,column:62},end:{line:1,column:71}}}):s))+'][optimization][]" multiple="multiple">\n'+(null!=(i=u(n,"each").call(o,null!=e?u(e,"optimizations"):e,{name:"each",hash:{},fn:l.program(1,a,0),inverse:l.noop,data:a,loc:{start:{line:2,column:4},end:{line:4,column:13}}}))?i:"")+"</select>"},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.schedule_types=this.WP_Optimize_Handlebars.schedule_types||{},this.WP_Optimize_Handlebars.schedule_types.handlebars=Handlebars.template({1:function(l,e,n,t,a){var i,s=null!=e?e:l.nullContext||{},o=l.hooks.helperMissing,u="function",r=l.escapeExpression,p=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'    <option value="'+r((i=null!=(i=p(n,"key")||a&&p(a,"key"))?i:o,typeof i===u?i.call(s,{name:"key",hash:{},data:a,loc:{start:{line:3,column:19},end:{line:3,column:27}}}):i))+'" '+r((i=null!=(i=p(n,"selected")||(null!=e?p(e,"selected"):e))?i:o,typeof i===u?i.call(s,{name:"selected",hash:{},data:a,loc:{start:{line:3,column:29},end:{line:3,column:41}}}):i))+">"+r(l.lambda(e,e))+"</option>\n"},compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s,o=null!=e?e:l.nullContext||{},u=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'<select class="wpo_schedule_type" name="wp-optimize-auto['+l.escapeExpression((s=null!=(s=u(n,"count")||(null!=e?u(e,"count"):e))?s:l.hooks.helperMissing,"function"==typeof s?s.call(o,{name:"count",hash:{},data:a,loc:{start:{line:1,column:57},end:{line:1,column:66}}}):s))+'][schedule_type]">\n'+(null!=(i=u(n,"each").call(o,null!=e?u(e,"schedule_types"):e,{name:"each",hash:{},fn:l.program(1,a,0),inverse:l.noop,data:a,loc:{start:{line:2,column:4},end:{line:4,column:13}}}))?i:"")+'</select>\n<div class="wpo_schedule_fields"></div>'},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.status=this.WP_Optimize_Handlebars.status||{},this.WP_Optimize_Handlebars.status.handlebars=Handlebars.template({compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s=l.lambda,o=l.escapeExpression,u=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'<div class="wpo_event_status">\n    <label><input type="checkbox" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][status]" value="1" '+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"status_value"):i,e))+">"+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"status"):i,e))+"</label>\n</div>"},useData:!0}),this.WP_Optimize_Handlebars=this.WP_Optimize_Handlebars||{},this.WP_Optimize_Handlebars.weekly=this.WP_Optimize_Handlebars.weekly||{},this.WP_Optimize_Handlebars.weekly.handlebars=Handlebars.template({1:function(l,e,n,t,a){var i,s=l.escapeExpression,o=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return'    <option value="'+s((i=null!=(i=o(n,"key")||a&&o(a,"key"))?i:l.hooks.helperMissing,"function"==typeof i?i.call(null!=e?e:l.nullContext||{},{name:"key",hash:{},data:a,loc:{start:{line:6,column:19},end:{line:6,column:27}}}):i))+'">'+s(l.lambda(e,e))+"</option>\n"},compiler:[8,">= 4.3.0"],main:function(l,e,n,t,a){var i,s=l.lambda,o=l.escapeExpression,u=l.lookupProperty||function(l,e){if(Object.prototype.hasOwnProperty.call(l,e))return l[e]};return"<label>"+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time"):i,e))+'\n<input type="time" class="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"class_name"):i,e))+'" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][time]" value="'+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"time_value"):i,e))+'">\n</label>\n<select class="wpo_week_days" name="wp-optimize-auto['+o(s(null!=(i=null!=e?u(e,"details"):e)?u(i,"count"):i,e))+'][day]">\n'+(null!=(i=u(n,"each").call(null!=e?e:l.nullContext||{},null!=(i=null!=e?u(e,"details"):e)?u(i,"week_days"):i,{name:"each",hash:{},fn:l.program(1,a,0),inverse:l.noop,data:a,loc:{start:{line:5,column:4},end:{line:7,column:13}}}))?i:"")+"</select>"},useData:!0});