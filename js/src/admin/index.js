flarum.core.app.initializers.add('bilgi42-flarum-autofollow-tags', () => {
  if (flarum.core.app.extensionData) {
    flarum.core.app.extensionData
      .for('bilgi42-flarum-autofollow-tags')
      .registerSetting(function () {
        const tags = flarum.core.app.store.all('tags');
        const selectedTags = JSON.parse(
          this.setting('bilgi42-autofollow-tags.tag_ids')() || '[]'
        );

        return (
          <div className="Form-group">
            <label>
              {flarum.core.app.translator.trans(
                'bilgi42-autofollow-tags.admin.tags_label',
                {},
                'Select tags to auto-follow for new users'
              )}
            </label>
            <div className="helpText">
              {flarum.core.app.translator.trans(
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
