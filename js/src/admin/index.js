import app from 'flarum/admin/app';

app.initializers.add('bilgi42-flarum-autofollow-tags', () => {
  // Debug: log what's available
  console.log('app object:', app);
  console.log('app.extensionData:', app.extensionData);
  console.log('flarum.core:', flarum.core);
  console.log('flarum.core.compat:', flarum.core.compat);


  app.extensionData
    .for('bilgi42-flarum-autofollow-tags')
    .registerSetting(function () {
      const tags = app.store.all('tags');
      const selectedTags = JSON.parse(
        this.setting('bilgi42-autofollow-tags.tag_ids')() || '[]'
      );

      return (
        <div className="Form-group">
          <label>
            {app.translator.trans(
              'bilgi42-autofollow-tags.admin.tags_label',
              {},
              'Select tags to auto-follow for new users'
            )}
          </label>
          <div className="helpText">
            {app.translator.trans(
              'bilgi42-autofollow-tags.admin.tags_help',
              {},
              'New users will automatically follow these tags'
            )}
          </div>

          <div className="TagSelection">
            {tags.map((tag) => {
              const isSelected = selectedTags.includes(tag.id());

              return (
                <label className="checkbox" key={tag.id()}>
                  <input
                    type="checkbox"
                    checked={isSelected}
                    onchange={(e) => {
                      let newSelected = [...selectedTags];
                      if (e.target.checked) {
                        newSelected.push(tag.id());
                      } else {
                        newSelected = newSelected.filter((id) => id !== tag.id());
                      }
                      this.setting('bilgi42-autofollow-tags.tag_ids')(
                        JSON.stringify(newSelected)
                      );
                      m.redraw();
                    }}
                  />
                  <span style={{ color: tag.color() }}>{tag.name()}</span>
                </label>
              );
            })}
          </div>
        </div>
      );
    });
});
