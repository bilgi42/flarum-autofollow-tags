// Use flarum.reg.get to access the admin app in Flarum 2.0
const app = flarum.reg.get('core', 'admin/app');

app.initializers.add('bilgi42-flarum-autofollow-tags', () => {
  console.log('=== REGISTRY DEBUG ===');
  console.log('app.registry:', app.registry);
  console.log('app.registry.for:', app.registry.for);

  const extensionData = app.registry.for('bilgi42-flarum-autofollow-tags');
  console.log('extensionData:', extensionData);
  console.log('extensionData.registerSetting:', extensionData?.registerSetting);
  console.log('extensionData methods:', extensionData ? Object.getOwnPropertyNames(Object.getPrototypeOf(extensionData)) : 'none');

  if (!extensionData || !extensionData.registerSetting) {
    console.error('Cannot register settings - method does not exist!');
    console.log('Available extensionData methods:', extensionData ? Object.keys(extensionData) : 'none');
    return;
  }

  // In Flarum 2.0, use app.registry instead of app.extensionData
  app.registry
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
