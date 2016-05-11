<input $AttributesHTML>
<input type="hidden" name="$Name" value="$dataValue">
<span class="value-holder<% if $Value %> has-value<% end_if %>">Selected: <em class="value" data-empty-val="(none)"><% if $Value %>$Value<% else %>(none)<% end_if %></em> <a href="#" class="clear">Clear ×</a></span>
