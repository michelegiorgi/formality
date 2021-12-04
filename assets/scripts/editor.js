// Formality editor scripts
import './editor/components/repeaterControl.js';
import { pageLoad } from './editor/utility/init.js';
import { formSidebar } from './editor/plugins/sidebar.js';
import { textBlock } from './editor/blocks/text.js';
import { textareaBlock } from './editor/blocks/textarea.js';
import { emailBlock } from './editor/blocks/email.js';
import { numberBlock } from './editor/blocks/number.js';
import { selectBlock } from './editor/blocks/select.js';
import { multipleBlock } from './editor/blocks/multiple.js';
import { switchBlock } from './editor/blocks/switch.js';
import { ratingBlock } from './editor/blocks/rating.js';
import { uploadBlock } from './editor/blocks/upload.js';
import { stepBlock } from './editor/blocks/step.js';
import { messageBlock } from './editor/blocks/message.js';
import { mediaBlock } from './editor/blocks/media.js';
import { widgetBlock } from './editor/blocks/widget.js';

if(formality.editor=='formality') {
  pageLoad()
  formSidebar()
  textBlock()
  textareaBlock()
  emailBlock()
  numberBlock()
  selectBlock()
  multipleBlock()
  switchBlock()
  ratingBlock()
  uploadBlock()
  stepBlock()
  messageBlock()
  mediaBlock()
} else {
  widgetBlock()
}
