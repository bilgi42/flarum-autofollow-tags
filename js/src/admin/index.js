import app from 'flarum/admin/app';

// Try both the new Admin extender and old app.extensionData for compatibility
export { default as extend } from './extend';

app.initializers.add('bilgi42-flarum-autofollow-tags', () => {
  // If app.extensionData still exists (backwards compatibility), use it
  if (app.extensionData) {
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
  }
});
