// Formality editor scripts
import './components/repeaterControl.js';
import { pageLoad } from './utility/init.js';
import { formSidebar } from './plugins/sidebar.js';
import { textBlock } from './blocks/text.js';
import { textareaBlock } from './blocks/textarea.js';
import { emailBlock } from './blocks/email.js';
import { numberBlock } from './blocks/number.js';
import { selectBlock } from './blocks/select.js';
import { multipleBlock } from './blocks/multiple.js';
import { switchBlock } from './blocks/switch.js';
import { ratingBlock } from './blocks/rating.js';
import { uploadBlock } from './blocks/upload.js';
import { stepBlock } from './blocks/step.js';
import { messageBlock } from './blocks/message.js';
import { mediaBlock } from './blocks/media.js';
import { widgetBlock } from './blocks/widget.js';

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
